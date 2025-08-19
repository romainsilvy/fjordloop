<?php

namespace Tests\Feature\Policies;

use App\Models\Activity;
use App\Models\Travel;
use App\Models\User;
use App\Policies\ActivityPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityPolicyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function user_can_view_any_activities()
    {
        $user = User::factory()->create();

        $this->assertTrue($user->can('viewAny', Activity::class));
    }

    /**
     * @test
     */
    public function member_can_view_activity()
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();
        $activity = Activity::factory()->forTravel($travel)->create();

        // Ajouter le membre au voyage
        $travel->members()->attach($member->id);

        $this->assertTrue($member->can('view', $activity));
    }

    /**
     * @test
     */
    public function non_member_cannot_view_activity()
    {
        $owner = User::factory()->create();
        $nonMember = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();
        $activity = Activity::factory()->forTravel($travel)->create();

        $this->assertFalse($nonMember->can('view', $activity));
    }

    /**
     * @test
     */
    public function member_can_update_activity()
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();
        $activity = Activity::factory()->forTravel($travel)->create();

        // Ajouter le membre au voyage
        $travel->members()->attach($member->id);

        $this->assertTrue($member->can('update', $activity));
    }

    /**
     * @test
     */
    public function member_can_delete_activity()
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();
        $activity = Activity::factory()->forTravel($travel)->create();

        // Ajouter le membre au voyage
        $travel->members()->attach($member->id);

        $this->assertTrue($member->can('delete', $activity));
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
    public function non_member_cannot_perform_actions()
    {
        $owner = User::factory()->create();
        $nonMember = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();
        $activity = Activity::factory()->forTravel($travel)->create();

        $this->assertFalse($nonMember->can('view', $activity));
        $this->assertFalse($nonMember->can('update', $activity));
        $this->assertFalse($nonMember->can('delete', $activity));
    }

    // Tests pour améliorer la couverture
    /**
     * @test
     */
    public function user_can_create_activity()
    {
        $user = User::factory()->create();

        $this->assertTrue($user->can('create', Activity::class));
    }

    /**
     * @test
     */
    public function member_can_restore_activity()
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();
        $activity = Activity::factory()->forTravel($travel)->create();

        // Ajouter le membre au voyage
        $travel->members()->attach($member->id);

        $this->assertTrue($member->can('restore', $activity));
    }

    /**
     * @test
     */
    public function member_can_force_delete_activity()
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();
        $activity = Activity::factory()->forTravel($travel)->create();

        // Ajouter le membre au voyage
        $travel->members()->attach($member->id);

        $this->assertTrue($member->can('forceDelete', $activity));
    }

    /**
     * @test
     */
    public function non_member_cannot_restore_activity()
    {
        $owner = User::factory()->create();
        $nonMember = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();
        $activity = Activity::factory()->forTravel($travel)->create();

        $this->assertFalse($nonMember->can('restore', $activity));
    }

    /**
     * @test
     */
    public function non_member_cannot_force_delete_activity()
    {
        $owner = User::factory()->create();
        $nonMember = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();
        $activity = Activity::factory()->forTravel($travel)->create();

        $this->assertFalse($nonMember->can('forceDelete', $activity));
    }

    /**
     * @test
     */
    public function owner_can_perform_all_actions_on_activity()
    {
        $owner = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();
        $activity = Activity::factory()->forTravel($travel)->create();

        $this->assertTrue($owner->can('view', $activity));
        $this->assertTrue($owner->can('update', $activity));
        $this->assertTrue($owner->can('delete', $activity));
        $this->assertTrue($owner->can('restore', $activity));
        $this->assertTrue($owner->can('forceDelete', $activity));
        $this->assertTrue($owner->can('createActivity', $travel));
    }

    /**
     * @test
     */
    public function member_can_create_activity_for_travel_using_create_for_travel()
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();

        // Ajouter le membre au voyage
        $travel->members()->attach($member->id);

        // Tester la méthode createForTravel directement sur la policy
        $policy = new ActivityPolicy;
        $this->assertTrue($policy->createForTravel($member, $travel));
    }

    /**
     * @test
     */
    public function non_member_cannot_create_activity_for_travel_using_create_for_travel()
    {
        $owner = User::factory()->create();
        $nonMember = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();

        // Tester la méthode createForTravel directement sur la policy
        $policy = new ActivityPolicy;
        $this->assertFalse($policy->createForTravel($nonMember, $travel));
    }
}
