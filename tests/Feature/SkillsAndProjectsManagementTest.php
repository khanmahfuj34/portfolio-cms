<?php

namespace Tests\Feature;

use App\Models\Portfolio;
use App\Models\PortfolioProject;
use App\Models\PortfolioSkill;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SkillsAndProjectsManagementTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;
    private Portfolio $portfolio;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        $this->portfolio = Portfolio::create([
            'user_id' => $this->admin->id,
            'name' => 'Test Architecture Portfolio',
            'slug' => 'test-arch-portfolio',
            'description' => 'Architectural showcase',
            'status' => 'draft',
            'published_at' => null,
        ]);
    }

    public function test_guest_cannot_access_skills_or_projects(): void
    {
        $this->getJson("/admin/portfolios/{$this->portfolio->id}/skills")
            ->assertStatus(401);

        $this->postJson("/admin/portfolios/{$this->portfolio->id}/skills", ['name' => 'Laravel'])
            ->assertStatus(401);

        $this->getJson("/admin/portfolios/{$this->portfolio->id}/projects")
            ->assertStatus(401);

        $this->postJson("/admin/portfolios/{$this->portfolio->id}/projects", ['title' => 'Project A'])
            ->assertStatus(401);
    }

    public function test_super_admin_can_view_skills_via_json(): void
    {
        PortfolioSkill::create([
            'portfolio_id' => $this->portfolio->id,
            'name' => 'PHP',
        ]);
        PortfolioSkill::create([
            'portfolio_id' => $this->portfolio->id,
            'name' => 'Laravel',
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson("/admin/portfolios/{$this->portfolio->id}/skills");

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonCount(2, 'data');
        $response->assertJsonFragment(['name' => 'PHP']);
        $response->assertJsonFragment(['name' => 'Laravel']);
    }

    public function test_super_admin_can_create_update_and_delete_skill(): void
    {
        // 1. Create
        $createRes = $this->actingAs($this->admin)
            ->postJson("/admin/portfolios/{$this->portfolio->id}/skills", [
                'name' => 'TypeScript',
            ]);

        $createRes->assertStatus(201);
        $createRes->assertJsonPath('success', true);
        $createRes->assertJsonPath('data.name', 'TypeScript');

        $skillId = $createRes->json('data.id');
        $this->assertDatabaseHas('portfolio_skills', [
            'id' => $skillId,
            'portfolio_id' => $this->portfolio->id,
            'name' => 'TypeScript',
        ]);

        // 2. Reject duplicate skill name in same portfolio
        $dupRes = $this->actingAs($this->admin)
            ->postJson("/admin/portfolios/{$this->portfolio->id}/skills", [
                'name' => 'TypeScript',
            ]);
        $dupRes->assertStatus(422);

        // 3. Update
        $updateRes = $this->actingAs($this->admin)
            ->putJson("/admin/portfolios/{$this->portfolio->id}/skills/{$skillId}", [
                'name' => 'TypeScript & React',
            ]);

        $updateRes->assertStatus(200);
        $updateRes->assertJsonPath('success', true);
        $this->assertDatabaseHas('portfolio_skills', [
            'id' => $skillId,
            'name' => 'TypeScript & React',
        ]);

        // 4. Delete
        $deleteRes = $this->actingAs($this->admin)
            ->deleteJson("/admin/portfolios/{$this->portfolio->id}/skills/{$skillId}");

        $deleteRes->assertStatus(200);
        $deleteRes->assertJsonPath('success', true);
        $this->assertDatabaseMissing('portfolio_skills', [
            'id' => $skillId,
        ]);
    }

    public function test_super_admin_can_create_update_and_delete_project(): void
    {
        // 1. Create
        $payload = [
            'title' => 'Cloud Orchestrator',
            'description' => 'A resilient distributed cloud orchestration service.',
            'thumbnail' => 'https://example.com/thumb.jpg',
            'live_url' => 'https://cloud.example.com',
            'github_url' => 'https://github.com/example/cloud',
            'technologies' => ['Laravel', 'Go', 'Docker', 'PostgreSQL'],
            'is_featured' => true,
            'is_visible' => true,
            'sort_order' => 1,
        ];

        $createRes = $this->actingAs($this->admin)
            ->postJson("/admin/portfolios/{$this->portfolio->id}/projects", $payload);

        $createRes->assertStatus(201);
        $createRes->assertJsonPath('success', true);
        $createRes->assertJsonPath('data.title', 'Cloud Orchestrator');

        $projectId = $createRes->json('data.id');
        $this->assertDatabaseHas('portfolio_projects', [
            'id' => $projectId,
            'portfolio_id' => $this->portfolio->id,
            'title' => 'Cloud Orchestrator',
            'is_featured' => 1,
            'is_visible' => 1,
        ]);

        // 2. Reject missing title
        $invalidRes = $this->actingAs($this->admin)
            ->postJson("/admin/portfolios/{$this->portfolio->id}/projects", [
                'title' => '',
            ]);
        $invalidRes->assertStatus(422);

        // 3. Update
        $updateRes = $this->actingAs($this->admin)
            ->putJson("/admin/portfolios/{$this->portfolio->id}/projects/{$projectId}", [
                'title' => 'Updated Cloud Orchestrator v2',
                'technologies' => ['Laravel', 'Kubernetes'],
                'is_featured' => false,
            ]);

        $updateRes->assertStatus(200);
        $updateRes->assertJsonPath('success', true);
        $this->assertDatabaseHas('portfolio_projects', [
            'id' => $projectId,
            'title' => 'Updated Cloud Orchestrator v2',
            'is_featured' => 0,
        ]);

        // 4. Delete
        $deleteRes = $this->actingAs($this->admin)
            ->deleteJson("/admin/portfolios/{$this->portfolio->id}/projects/{$projectId}");

        $deleteRes->assertStatus(200);
        $deleteRes->assertJsonPath('success', true);
        $this->assertDatabaseMissing('portfolio_projects', [
            'id' => $projectId,
        ]);
    }

    public function test_cannot_access_or_modify_other_portfolios_skills_or_projects(): void
    {
        $otherPortfolio = Portfolio::create([
            'user_id' => $this->admin->id,
            'name' => 'Other Portfolio',
            'slug' => 'other-portfolio',
            'status' => 'draft',
        ]);

        $otherSkill = PortfolioSkill::create([
            'portfolio_id' => $otherPortfolio->id,
            'name' => 'Ruby',
        ]);

        $otherProject = PortfolioProject::create([
            'portfolio_id' => $otherPortfolio->id,
            'title' => 'Other Project',
        ]);

        // Scoped bindings prevent cross-portfolio access
        $this->actingAs($this->admin)
            ->putJson("/admin/portfolios/{$this->portfolio->id}/skills/{$otherSkill->id}", ['name' => 'Hacked'])
            ->assertStatus(404);

        $this->actingAs($this->admin)
            ->deleteJson("/admin/portfolios/{$this->portfolio->id}/skills/{$otherSkill->id}")
            ->assertStatus(404);

        $this->actingAs($this->admin)
            ->putJson("/admin/portfolios/{$this->portfolio->id}/projects/{$otherProject->id}", ['title' => 'Hacked'])
            ->assertStatus(404);

        $this->actingAs($this->admin)
            ->deleteJson("/admin/portfolios/{$this->portfolio->id}/projects/{$otherProject->id}")
            ->assertStatus(404);
    }

    public function test_portfolio_edit_view_renders_skills_and_projects_sections(): void
    {
        PortfolioSkill::create([
            'portfolio_id' => $this->portfolio->id,
            'name' => 'DevOps',
        ]);

        PortfolioProject::create([
            'portfolio_id' => $this->portfolio->id,
            'title' => 'Platform Engineering Suite',
            'technologies' => ['Terraform', 'AWS'],
        ]);

        $response = $this->actingAs($this->admin)
            ->get("/admin/portfolios/{$this->portfolio->id}/edit");

        $response->assertStatus(200);
        $response->assertSee('Skills Configuration');
        $response->assertSee('Projects Configuration');
        $response->assertSee('DevOps');
        $response->assertSee('Platform Engineering Suite');
        $response->assertSee('Terraform');
    }

    public function test_create_portfolio_view_contains_skills_and_projects_sections(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/portfolios/create');

        $response->assertStatus(200);
        $response->assertSee('Navbar Configuration');
        $response->assertSee('Skills Configuration');
        $response->assertSee('Projects Configuration');
        $response->assertSee('Add Skill');
        $response->assertSee('Add Project');
        $response->assertDontSee('Save portfolio to manage skills');
        $response->assertDontSee('Save portfolio to manage projects');
    }

    public function test_store_portfolio_with_skills_and_projects_creates_all_in_one_transaction(): void
    {
        $slug = 'portfolio-' . uniqid();
        $payload = [
            'name' => 'Fullstack Mastery Portfolio',
            'slug' => $slug,
            'description' => 'Showcase with skills and projects.',
            'status' => 'draft',
            'full_name' => 'Sarah Connor',
            'professional_title' => 'Systems Architect',
            'email' => 'sarah@example.com',
            'location' => 'Los Angeles, CA',
            'bio' => 'Building resilient applications.',
            'nav_items' => [
                [
                    'label' => 'Home',
                    'type' => 'section',
                    'section_key' => 'home',
                    'sort_order' => 1,
                    'is_visible' => true,
                ],
                [
                    'label' => 'Projects',
                    'type' => 'section',
                    'section_key' => 'projects',
                    'sort_order' => 2,
                    'is_visible' => true,
                ],
            ],
            'skills' => [
                ['name' => 'Laravel'],
                ['name' => 'PostgreSQL'],
                ['name' => 'Vue.js'],
            ],
            'projects' => [
                [
                    'title' => 'High-Throughput Ingestion Engine',
                    'description' => 'Kafka and Laravel streaming engine.',
                    'thumbnail' => 'https://example.com/engine.jpg',
                    'live_url' => 'https://engine.example.com',
                    'github_url' => 'https://github.com/example/engine',
                    'technologies' => ['PHP', 'Kafka', 'Redis'],
                    'is_featured' => true,
                    'is_visible' => true,
                    'sort_order' => 1,
                    'portfolio_id' => 99999, // Attempted spoofing must be ignored
                ],
                [
                    'title' => 'Internal Observability Dashboard',
                    'description' => 'Grafana & Prometheus bridge.',
                    'thumbnail' => null,
                    'live_url' => null,
                    'github_url' => 'https://github.com/example/dash',
                    'technologies' => 'Prometheus, Go', // Comma separated string normalized by request
                    'is_featured' => false,
                    'is_visible' => true,
                    'sort_order' => 2,
                ],
            ],
        ];

        $response = $this->actingAs($this->admin)
            ->post('/admin/portfolios', $payload);

        $response->assertStatus(302);
        $response->assertRedirect('/admin/profiles');
        $response->assertSessionHas('success', 'Portfolio created successfully.');

        $newPortfolio = Portfolio::where('slug', $slug)->firstOrFail();
        $this->assertEquals('draft', $newPortfolio->status);
        $this->assertEquals($this->admin->id, $newPortfolio->user_id);

        // Verify Skills were created and correctly scoped to the new portfolio
        $this->assertCount(3, $newPortfolio->skills);
        $this->assertEquals(['Laravel', 'PostgreSQL', 'Vue.js'], $newPortfolio->skills->pluck('name')->all());
        foreach ($newPortfolio->skills as $skill) {
            $this->assertEquals($newPortfolio->id, $skill->portfolio_id);
        }

        // Verify Projects were created, normalized and scoped
        $this->assertCount(2, $newPortfolio->projects);
        $project1 = $newPortfolio->projects()->where('title', 'High-Throughput Ingestion Engine')->firstOrFail();
        $this->assertEquals($newPortfolio->id, $project1->portfolio_id);
        $this->assertNotEquals(99999, $project1->portfolio_id);
        $this->assertTrue((bool) $project1->is_featured);
        $this->assertEquals(['PHP', 'Kafka', 'Redis'], $project1->technologies);

        $project2 = $newPortfolio->projects()->where('title', 'Internal Observability Dashboard')->firstOrFail();
        $this->assertEquals($newPortfolio->id, $project2->portfolio_id);
        $this->assertFalse((bool) $project2->is_featured);
        $this->assertEquals(['Prometheus', 'Go'], $project2->technologies);
    }

    public function test_validation_failure_on_skills_creates_no_records(): void
    {
        $slug = 'failed-skill-slug-' . uniqid();
        $payload = [
            'name' => 'Failing Skills Portfolio',
            'slug' => $slug,
            'full_name' => 'John Doe',
            'nav_items' => [
                [
                    'label' => 'Home',
                    'type' => 'section',
                    'section_key' => 'home',
                    'sort_order' => 1,
                    'is_visible' => true,
                ],
            ],
            'skills' => [
                ['name' => 'DuplicateSkill'],
                ['name' => 'duplicateskill'], // Duplicate case-insensitive
            ],
        ];

        $response = $this->actingAs($this->admin)
            ->from('/admin/portfolios/create')
            ->post('/admin/portfolios', $payload);

        $response->assertStatus(302);
        $response->assertRedirect('/admin/portfolios/create');
        $response->assertSessionHasErrors(['skills.0.name']);

        // Verify no records created
        $this->assertDatabaseMissing('portfolios', ['slug' => $slug]);
        $this->assertDatabaseMissing('portfolio_skills', ['name' => 'DuplicateSkill']);
    }

    public function test_validation_failure_on_projects_creates_no_records_and_rehydrates_old_input(): void
    {
        $slug = 'failed-proj-slug-' . uniqid();
        $payload = [
            'name' => 'Failing Project Portfolio',
            'slug' => $slug,
            'full_name' => 'John Doe',
            'nav_items' => [
                [
                    'label' => 'Home',
                    'type' => 'section',
                    'section_key' => 'home',
                    'sort_order' => 1,
                    'is_visible' => true,
                ],
            ],
            'skills' => [
                ['name' => 'Rust'],
            ],
            'projects' => [
                [
                    'title' => 'Invalid URL Project',
                    'live_url' => 'not-a-valid-url',
                ],
            ],
        ];

        $response = $this->actingAs($this->admin)
            ->from('/admin/portfolios/create')
            ->post('/admin/portfolios', $payload);

        $response->assertStatus(302);
        $response->assertRedirect('/admin/portfolios/create');
        $response->assertSessionHasErrors(['projects.0.live_url']);

        // Verify no records created
        $this->assertDatabaseMissing('portfolios', ['slug' => $slug]);
        $this->assertDatabaseMissing('portfolio_skills', ['name' => 'Rust']);
        $this->assertDatabaseMissing('portfolio_projects', ['title' => 'Invalid URL Project']);
    }
}
