<?php

namespace Tests\Unit;

use DTApi\Repository\UserRepository;
use DTApi\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected $userRepository;
    protected $faker;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepository = new UserRepository(new User);
        $this->faker = Faker::create();
    }

    /** @test */
    public function it_creates_a_new_user_with_all_fields()
    {
        $request = [
            'role' => rand(1, 2),
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'password' => $this->faker->randomNumber(8),
            'company_id' => 1,
            'department_id' => 1,
            'dob_or_orgid' => $this->faker->randomNumber(9),
            'phone' => $this->faker->phoneNumber,
            'mobile' => $this->faker->phoneNumber,
            'consumer_type' => 'paid',
            'customer_type' => $this->faker->word,
            'username' => $this->faker->userName,
            'post_code' => $this->faker->postcode,
            'address' => $this->faker->address,
            'city' => $this->faker->city,
            'town' => $this->faker->word,
            'country' => $this->faker->country,
            'reference' => $this->faker->boolean ? '1' : '0',
            'additional_info' => $this->faker->sentence,
            'cost_place' => $this->faker->word,
            'fee' => $this->faker->randomFloat(2, 0, 1000),
            'time_to_charge' => $this->faker->numberBetween(1, 60),
            'time_to_pay' => $this->faker->numberBetween(1, 60),
            'charge_ob' => $this->faker->word,
            'customer_id' => $this->faker->word,
            'charge_km' => $this->faker->randomFloat(2, 0, 100),
            'maximum_km' => $this->faker->randomFloat(2, 0, 1000),
            'translator_type' => null,
            'worked_for' => null,
            'organization_number' => null,
            'gender' => 'male',
            'translator_level' => null,
            'user_language' => [],
            'user_towns_projects' => [],
            'status' => '1',
            'address_2' => $this->faker->address,
            'new_towns' => $this->faker->word
        ];

        $user = $this->userRepository->createOrUpdate(null, $request);
        $this->user = $user;
        $this->assertInstanceOf(User::class, $user);
    }

    /** @test */
    public function it_updates_an_existing_user()
    {
        if (!$this->user) {
            $this->it_creates_a_new_user_with_all_fields();
        }
        $updateRequest  = [
            'role' => rand(1, 2),
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'password' => $this->faker->randomNumber(8),
            'company_id' => 1,
            'department_id' => 1,
            'dob_or_orgid' => $this->faker->randomNumber(9),
            'phone' => $this->faker->phoneNumber,
            'mobile' => $this->faker->phoneNumber,
            'consumer_type' => 'paid',
            'customer_type' => $this->faker->word,
            'username' => $this->faker->userName,
            'post_code' => $this->faker->postcode,
            'address' => $this->faker->address,
            'city' => $this->faker->city,
            'town' => $this->faker->word,
            'country' => $this->faker->country,
            'reference' => $this->faker->boolean ? '1' : '0',
            'additional_info' => $this->faker->sentence,
            'cost_place' => $this->faker->word,
            'fee' => $this->faker->randomFloat(2, 0, 1000),
            'time_to_charge' => $this->faker->numberBetween(1, 60),
            'time_to_pay' => $this->faker->numberBetween(1, 60),
            'charge_ob' => $this->faker->word,
            'customer_id' => $this->faker->word,
            'charge_km' => $this->faker->randomFloat(2, 0, 100),
            'maximum_km' => $this->faker->randomFloat(2, 0, 1000),
            'translator_type' => null,
            'worked_for' => null,
            'organization_number' => null,
            'gender' => null,
            'translator_level' => null,
            'user_language' => [],
            'user_towns_projects' => [],
            'status' => '1',
            'address_2' => $this->faker->address,
            'new_towns' => $this->faker->word
        ];

        $updatedUser = $this->userRepository->createOrUpdate($this->user->id, $updateRequest);

        $this->assertInstanceOf(User::class, $updatedUser);
    }

    /** @test */
    public function it_enables_a_user()
    {
        if (!$this->user) {
            $this->it_creates_a_new_user_with_all_fields();
        }

        $this->userRepository->updateStatus($this->user->id, '1');

        $this->user->refresh();
        $this->assertEquals('1', $this->user->status);
    }

    /** @test */
    public function it_disables_a_user()
    {
        if (!$this->user) {
            $this->it_creates_a_new_user_with_all_fields();
        }

        $this->userRepository->updateStatus($this->user->id, '0');

        $this->user->refresh();
        $this->assertEquals('0', $this->user->status);
    }
}
