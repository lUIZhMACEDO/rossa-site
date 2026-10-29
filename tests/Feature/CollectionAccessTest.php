<?php

use App\Enums\PermissionType;
use App\Models\Collection;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function userWithCollectionPermissions(array $permissions): User
{
    $role = Role::query()->create([
        'name' => 'collection-access-'.uniqid(),
        'is_custom' => true,
    ]);

    foreach ($permissions as $permission) {
        $record = Permission::query()->firstOrCreate(['name' => $permission]);
        $role->permissions()->attach($record);
    }

    return User::factory()->create([
        'role_id' => $role->id,
    ]);
}

function collectionOwnedBy(User $owner, string $status, string $title): Collection
{
    return Collection::query()->create([
        'user_id' => $owner->id,
        'title' => $title,
        'status' => $status,
    ]);
}

function makeCorrespondingInvestigator(Collection $collection, User $user): void
{
    $collection->investigators()->create([
        'user_id' => $user->id,
        'first_name' => 'Ada',
        'last_name' => 'Lovelace',
        'email' => $user->email,
        'corresponding' => true,
        'is_contributing' => false,
    ]);
}

it('renders an approved collection without editor controls for visitors', function () {
    $owner = User::factory()->create();
    $collection = collectionOwnedBy($owner, 'approved', 'Public bone screen');
    makeCorrespondingInvestigator($collection, $owner);

    $this->get(route('collections.show', $collection))
        ->assertSee('Public bone screen')
        ->assertDontSee('Save Changes')
        ->assertDontSee('Open the collection editor')
        ->assertDontSee('removeInvestigator')
        ->assertDontSee('Edit collection information');
});

it('hides an unapproved collection from visitors', function () {
    $owner = User::factory()->create();
    $collection = collectionOwnedBy($owner, 'draft', 'Draft bone screen');

    $this->get(route('collections.show', $collection))
        ->assertNotFound();
});

it('shows a collection manager the public report without save controls', function () {
    $manager = userWithCollectionPermissions([PermissionType::MANAGE_COLLECTIONS->value]);
    $collection = collectionOwnedBy($manager, 'approved', 'Managed bone screen');

    $this->actingAs($manager)
        ->get(route('collections.show', $collection))
        ->assertSee('Managed bone screen')
        ->assertSee('Open the collection editor')
        ->assertDontSee('Save Changes')
        ->assertDontSee('removeInvestigator');
});

it('renders save and investigator controls on the collection editor', function () {
    $manager = userWithCollectionPermissions([PermissionType::MANAGE_COLLECTIONS->value]);
    $collection = collectionOwnedBy($manager, 'approved', 'Managed bone screen');
    makeCorrespondingInvestigator($collection, $manager);

    $this->actingAs($manager)
        ->get(route('admin.collections.edit', $collection))
        ->assertSee('Save Changes')
        ->assertSee('This is the collection editor')
        ->assertSee('removeInvestigator');
});

it('lets a collection manager update an approved collection', function () {
    $manager = userWithCollectionPermissions([PermissionType::MANAGE_COLLECTIONS->value]);
    $collection = collectionOwnedBy($manager, 'approved', 'Managed bone screen');

    $this->actingAs($manager)
        ->put(route('admin.collections.update', $collection), [
            'title' => 'Corrected public title',
        ])
        ->assertRedirect(route('admin.collections.edit', $collection));

    $this->assertDatabaseHas('collections', [
        'id' => $collection->id,
        'title' => 'Corrected public title',
    ]);
});

it('lets a corresponding investigator update a draft collection', function () {
    $investigator = userWithCollectionPermissions([PermissionType::EDIT_OWN_COLLECTIONS->value]);
    $collection = collectionOwnedBy($investigator, 'draft', 'Draft bone screen');
    makeCorrespondingInvestigator($collection, $investigator);

    $this->actingAs($investigator)
        ->put(route('my-collections.update', $collection), [
            'title' => 'Investigator title',
        ])
        ->assertRedirect(route('my-collections.edit', $collection));

    $this->assertDatabaseHas('collections', [
        'id' => $collection->id,
        'title' => 'Investigator title',
    ]);
});

it('forbids a corresponding investigator from saving an approved collection', function () {
    $investigator = userWithCollectionPermissions([PermissionType::EDIT_OWN_COLLECTIONS->value]);
    $collection = collectionOwnedBy($investigator, 'approved', 'Published collection');
    makeCorrespondingInvestigator($collection, $investigator);

    $this->actingAs($investigator)
        ->put(route('my-collections.update', $collection), [
            'title' => 'Changed by investigator',
        ])
        ->assertForbidden();

    $this->assertDatabaseHas('collections', [
        'id' => $collection->id,
        'title' => 'Published collection',
    ]);
});

it('shows a corresponding investigator a read-only editor after approval', function () {
    $investigator = userWithCollectionPermissions([PermissionType::EDIT_OWN_COLLECTIONS->value]);
    $collection = collectionOwnedBy($investigator, 'approved', 'Published collection');
    makeCorrespondingInvestigator($collection, $investigator);

    $this->actingAs($investigator)
        ->get(route('my-collections.edit', $collection))
        ->assertSee('Changes cannot be saved')
        ->assertDontSee('Save Changes')
        ->assertDontSee('removeInvestigator');
});

it('forbids someone who is not on the collection from opening the editor', function () {
    $owner = User::factory()->create();
    $stranger = userWithCollectionPermissions([PermissionType::EDIT_OWN_COLLECTIONS->value]);
    $collection = collectionOwnedBy($owner, 'draft', 'Someone else\'s collection');
    makeCorrespondingInvestigator($collection, $owner);

    $this->actingAs($stranger)
        ->get(route('my-collections.edit', $collection))
        ->assertForbidden();
});
