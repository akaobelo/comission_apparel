<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\DesignCatalog;
use App\Models\TeamStore;
use App\Models\StoreItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

class BulkDeleteDesignsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_bulk_delete_unassigned_designs()
    {
        Storage::fake('public');

        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $coach = User::factory()->create([
            'role' => 'coach',
        ]);

        $store = TeamStore::create([
            'user_id' => $coach->id,
            'name' => 'Test Store',
            'slug' => 'test-store',
            'status' => 'active',
        ]);

        // Upload fake images
        Storage::disk('public')->put('designs/test1.png', 'fake image 1');
        Storage::disk('public')->put('designs/test2.png', 'fake image 2');

        $design1 = DesignCatalog::create([
            'name' => 'Arm Sleeves #1',
            'sport' => 'Basketball',
            'category' => 'individual',
            'types' => ['arm_sleeve'],
            'image_paths' => ['/storage/designs/test1.png'],
            'wholesale_price' => 15.00,
        ]);

        $design2 = DesignCatalog::create([
            'name' => 'Arm Sleeves #2',
            'sport' => 'Basketball',
            'category' => 'individual',
            'types' => ['arm_sleeve'],
            'image_paths' => ['/storage/designs/test2.png'],
            'wholesale_price' => 15.00,
        ]);

        $design3 = DesignCatalog::create([
            'name' => 'Arm Sleeves #3 (Keep)',
            'sport' => 'Basketball',
            'category' => 'individual',
            'types' => ['arm_sleeve'],
            'wholesale_price' => 15.00,
        ]);

        // Associate design1 with coach store item
        StoreItem::create([
            'team_store_id' => $store->id,
            'design_catalog_id' => $design1->id,
            'name' => $design1->name,
            'price' => 20.00,
            'sizes' => ['S', 'M'],
        ]);

        // Associate design2 with coach pivot
        $coach->designCatalog()->attach($design2->id);

        $this->assertDatabaseHas('design_catalog', ['id' => $design1->id]);
        $this->assertDatabaseHas('design_catalog', ['id' => $design2->id]);
        $this->assertDatabaseHas('design_catalog', ['id' => $design3->id]);
        $this->assertDatabaseHas('store_items', ['design_catalog_id' => $design1->id]);

        $response = $this->actingAs($admin)
            ->delete(route('admin.design.bulk-delete'), [
                'design_ids' => [$design1->id, $design2->id],
            ]);

        $response->assertRedirect(route('admin.dashboard'));
        $response->assertSessionHas('success');

        // Check designs were deleted
        $this->assertDatabaseMissing('design_catalog', ['id' => $design1->id]);
        $this->assertDatabaseMissing('design_catalog', ['id' => $design2->id]);
        $this->assertDatabaseHas('design_catalog', ['id' => $design3->id]);

        // Check associated store items were deleted
        $this->assertDatabaseMissing('store_items', ['design_catalog_id' => $design1->id]);

        // Check files were removed from disk
        Storage::disk('public')->assertMissing('designs/test1.png');
        Storage::disk('public')->assertMissing('designs/test2.png');
    }

    public function test_bulk_delete_validates_input()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)
            ->delete(route('admin.design.bulk-delete'), [
                'design_ids' => [],
            ]);

        $response->assertSessionHasErrors(['design_ids']);
    }
}
