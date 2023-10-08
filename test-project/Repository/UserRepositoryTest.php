<?php

use PHPUnit\Framework\TestCase;
use DTApi\Models\User;

class UserRepository extends TestCase
{
    public function testCreateOrUpdate()
    {

        $request = [
            'role' => 'customer',
            'name' => 'Test User',
            'company_id' => 1,
            'department_id' => 2,
            'email' => 'test@example.com',

        ];
        $user = new UserRepository();
        $result = $user->createOrUpdate(null, $request);

        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals('Test User', $result->name);
        $this->assertEquals(1, $result->company_id);
        $this->assertEquals('test@example.com', $result->email);
    }
}
