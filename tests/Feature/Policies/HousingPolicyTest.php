<?php

namespace Tests\Feature\Policies;

use App\Models\Housing;
use App\Models\Travel;
use App\Models\User;
use App\Policies\HousingPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HousingPolicyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function user_can_view_any_housings()
    {
        $user = User::factory()->create();

        $this->assertTrue($user->can('viewAny', Housing::class));
    }

    /**
     * @test
     */
    public function member_can_view_housing()
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();
        $housing = Housing::factory()->forTravel($travel)->create();

        // Ajouter le membre au voyage
        $travel->members()->attach($member->id);

        $this->assertTrue($member->can('view', $housing));
    }

    /**
     * @test
     */
    public function non_member_cannot_view_housing()
    {
        $owner = User::factory()->create();
        $nonMember = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();
        $housing = Housing::factory()->forTravel($travel)->create();

        $this->assertFalse($nonMember->can('view', $housing));
    }

    /**
     * @test
     */
    public function member_can_update_housing()
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();
        $housing = Housing::factory()->forTravel($travel)->create();

        // Ajouter le membre au voyage
        $travel->members()->attach($member->id);

        $this->assertTrue($member->can('update', $housing));
    }

    /**
     * @test
     */
    public function member_can_delete_housing()
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();
        $housing = Housing::factory()->forTravel($travel)->create();

        // Ajouter le membre au voyage
        $travel->members()->attach($member->id);

        $this->assertTrue($member->can('delete', $housing));
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
    public function non_member_cannot_perform_actions()
    {
        $owner = User::factory()->create();
        $nonMember = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();
        $housing = Housing::factory()->forTravel($travel)->create();

        $this->assertFalse($nonMember->can('view', $housing));
        $this->assertFalse($nonMember->can('update', $housing));
        $this->assertFalse($nonMember->can('delete', $housing));
    }

    // Tests pour améliorer la couverture
    /**
     * @test
     */
    public function user_can_create_housing()
    {
        $user = User::factory()->create();

        $this->assertTrue($user->can('create', Housing::class));
    }

    /**
     * @test
     */
    public function member_can_restore_housing()
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();
        $housing = Housing::factory()->forTravel($travel)->create();

        // Ajouter le membre au voyage
        $travel->members()->attach($member->id);

        $this->assertTrue($member->can('restore', $housing));
    }

    /**
     * @test
     */
    public function member_can_force_delete_housing()
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();
        $housing = Housing::factory()->forTravel($travel)->create();

        // Ajouter le membre au voyage
        $travel->members()->attach($member->id);

        $this->assertTrue($member->can('forceDelete', $housing));
    }

    /**
     * @test
     */
    public function non_member_cannot_restore_housing()
    {
        $owner = User::factory()->create();
        $nonMember = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();
        $housing = Housing::factory()->forTravel($travel)->create();

        $this->assertFalse($nonMember->can('restore', $housing));
    }

    /**
     * @test
     */
    public function non_member_cannot_force_delete_housing()
    {
        $owner = User::factory()->create();
        $nonMember = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();
        $housing = Housing::factory()->forTravel($travel)->create();

        $this->assertFalse($nonMember->can('forceDelete', $housing));
    }

    /**
     * @test
     */
    public function owner_can_perform_all_actions_on_housing()
    {
        $owner = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();
        $housing = Housing::factory()->forTravel($travel)->create();

        $this->assertTrue($owner->can('view', $housing));
        $this->assertTrue($owner->can('update', $housing));
        $this->assertTrue($owner->can('delete', $housing));
        $this->assertTrue($owner->can('restore', $housing));
        $this->assertTrue($owner->can('forceDelete', $housing));
        $this->assertTrue($owner->can('createHousing', $travel));
    }

    /**
     * @test
     */
    public function member_can_create_housing_for_travel_using_create_for_travel()
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();

        // Ajouter le membre au voyage
        $travel->members()->attach($member->id);

        // Tester la méthode createForTravel directement sur la policy
        $policy = new HousingPolicy;
        $this->assertTrue($policy->createForTravel($member, $travel));
    }

    /**
     * @test
     */
    public function non_member_cannot_create_housing_for_travel_using_create_for_travel()
    {
        $owner = User::factory()->create();
        $nonMember = User::factory()->create();
        $travel = Travel::factory()->withOwner($owner)->create();

        // Tester la méthode createForTravel directement sur la policy
        $policy = new HousingPolicy;
        $this->assertFalse($policy->createForTravel($nonMember, $travel));
    }
}
