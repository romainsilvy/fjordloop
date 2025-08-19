<?php

namespace Tests\Feature\Policies;

use App\Models\Travel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TravelPolicyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function user_can_view_any_travels()
    {
        $user = User::factory()->create();

        $this->assertTrue($user->can('viewAny', Travel::class));
    }

    /**
     * @test
     */
    public function user_can_create_travel()
    {
        $user = User::factory()->create();

        $this->assertTrue($user->can('create', Travel::class));
    }

    /**
     * @test
     */
    public function member_can_view_travel()
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();

        // Ajouter le membre au voyage
        $travel->members()->attach($member->id);

        $this->assertTrue($member->can('view', $travel));
    }

    /**
     * @test
     */
    public function non_member_cannot_view_travel()
    {
        $owner = User::factory()->create();
        $nonMember = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();

        $this->assertFalse($nonMember->can('view', $travel));
    }

    /**
     * @test
     */
    public function member_can_update_travel()
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();

        // Ajouter le membre au voyage
        $travel->members()->attach($member->id);

        $this->assertTrue($member->can('update', $travel));
    }

    /**
     * @test
     */
    public function member_can_delete_travel()
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();

        // Ajouter le membre au voyage
        $travel->members()->attach($member->id);

        $this->assertTrue($member->can('delete', $travel));
    }

    /**
     * @test
     */
    public function member_can_invite_members()
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();

        // Ajouter le membre au voyage
        $travel->members()->attach($member->id);

        $this->assertTrue($member->can('inviteMembers', $travel));
    }

    /**
     * @test
     */
    public function member_can_manage_members()
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();

        // Ajouter le membre au voyage
        $travel->members()->attach($member->id);

        $this->assertTrue($member->can('manageMembers', $travel));
    }

    /**
     * @test
     */
    public function non_member_cannot_perform_actions()
    {
        $owner = User::factory()->create();
        $nonMember = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();

        $this->assertFalse($nonMember->can('view', $travel));
        $this->assertFalse($nonMember->can('update', $travel));
        $this->assertFalse($nonMember->can('delete', $travel));
        $this->assertFalse($nonMember->can('inviteMembers', $travel));
        $this->assertFalse($nonMember->can('manageMembers', $travel));
    }

    // Tests pour améliorer la couverture
    /**
     * @test
     */
    public function member_can_restore_travel()
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();

        // Ajouter le membre au voyage
        $travel->members()->attach($member->id);

        $this->assertTrue($member->can('restore', $travel));
    }

    /**
     * @test
     */
    public function member_can_force_delete_travel()
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();

        // Ajouter le membre au voyage
        $travel->members()->attach($member->id);

        $this->assertTrue($member->can('forceDelete', $travel));
    }

    /**
     * @test
     */
    public function non_member_cannot_restore_travel()
    {
        $owner = User::factory()->create();
        $nonMember = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();

        $this->assertFalse($nonMember->can('restore', $travel));
    }

    /**
     * @test
     */
    public function non_member_cannot_force_delete_travel()
    {
        $owner = User::factory()->create();
        $nonMember = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();

        $this->assertFalse($nonMember->can('forceDelete', $travel));
    }

    /**
     * @test
     */
    public function member_can_create_activity_for_travel()
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();

        // Ajouter le membre au voyage
        $travel->members()->attach($member->id);

        $this->assertTrue($member->can('createActivity', $travel));
    }

    /**
     * @test
     */
    public function member_can_create_housing_for_travel()
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();

        // Ajouter le membre au voyage
        $travel->members()->attach($member->id);

        $this->assertTrue($member->can('createHousing', $travel));
    }

    /**
     * @test
     */
    public function non_member_cannot_create_activity_for_travel()
    {
        $owner = User::factory()->create();
        $nonMember = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();

        $this->assertFalse($nonMember->can('createActivity', $travel));
    }

    /**
     * @test
     */
    public function non_member_cannot_create_housing_for_travel()
    {
        $owner = User::factory()->create();
        $nonMember = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();

        $this->assertFalse($nonMember->can('createHousing', $travel));
    }

    /**
     * @test
     */
    public function owner_can_perform_all_actions_on_travel()
    {
        $owner = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();

        $this->assertTrue($owner->can('view', $travel));
        $this->assertTrue($owner->can('update', $travel));
        $this->assertTrue($owner->can('delete', $travel));
        $this->assertTrue($owner->can('restore', $travel));
        $this->assertTrue($owner->can('forceDelete', $travel));
        $this->assertTrue($owner->can('inviteMembers', $travel));
        $this->assertTrue($owner->can('manageMembers', $travel));
        $this->assertTrue($owner->can('createActivity', $travel));
        $this->assertTrue($owner->can('createHousing', $travel));
    }
}
