<?php

namespace App\Tests\Api\ActivityResponsibles;

use App\Tests\Api\ECampApiTestCase;

/**
 * @internal
 */
class ListActivityResponsiblesTest extends ECampApiTestCase {
    public function testListActivityResponsiblesIsDeniedForAnonymousUser() {
        static::createClient()->request('GET', '/activity_responsibles');
        $this->assertResponseStatusCodeSame(401);
        $this->assertJsonContains([
            'code' => 401,
            'message' => 'JWT Token not found',
        ]);
    }

    public function testListActivityResponsiblesIsAllowedForCollaboratorButFiltered() {
        // precondition: There is a period that the user doesn't have access to
        $this->assertNotEmpty(static::$fixtures['activityResponsible1campUnrelated']);

        $response = static::createClientWithCredentials()->request('GET', '/activity_responsibles');
        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains([
            'totalItems' => 3,
            '_links' => [
                'items' => [],
            ],
            '_embedded' => [
                'items' => [],
            ],
        ]);
        $this->assertEqualsCanonicalizing([
            ['href' => $this->getIriFor('activityResponsible1')],
            ['href' => $this->getIriFor('activityResponsible2')],
            ['href' => $this->getIriFor('activityResponsible1campPrototype')],
        ], $response->toArray()['_links']['items']);
    }

    public function testListActivityResponsiblesFilteredByActivityIsAllowedForCollaborator() {
        $activity = static::$fixtures['activity1'];
        $response = static::createClientWithCredentials()->request('GET', '/activity_responsibles?activity=/activities/'.$activity->getId());
        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains([
            'totalItems' => 1,
            '_links' => [
                'items' => [],
            ],
            '_embedded' => [
                'items' => [],
            ],
        ]);
        $this->assertEqualsCanonicalizing([
            ['href' => $this->getIriFor('activityResponsible1')],
        ], $response->toArray()['_links']['items']);
    }

    public function testListActivityResponsiblesFilteredByActivityIsDeniedForUnrelatedUser() {
        $activity = static::$fixtures['activity1'];
        $response = static::createClientWithCredentials(['username' => static::$fixtures['user4unrelated']->username])
            ->request('GET', '/activity_responsibles?activity=/activities/'.$activity->getId())
        ;

        $this->assertResponseStatusCodeSame(200);

        $this->assertJsonContains(['totalItems' => 0]);
        $this->assertStringNotContainsString($this->getIriFor('activityResponsible1'), $response->getContent());
    }

    public function testListActivityResponsiblesFilteredByActivityInCampPrototypeIsAllowedForUnrelatedUser() {
        $activity = static::$fixtures['activity1campPrototype'];
        $response = static::createClientWithCredentials()->request('GET', '/activity_responsibles?activity=/activities/'.$activity->getId());

        $this->assertResponseStatusCodeSame(200);

        $this->assertJsonContains(['totalItems' => 1]);
        $this->assertEqualsCanonicalizing([
            ['href' => $this->getIriFor('activityResponsible1campPrototype')],
        ], $response->toArray()['_links']['items']);
    }

    public function testListActivityResponsiblesFilteredByCampIsAllowedForCollaborator() {
        $camp = static::$fixtures['camp1'];
        $response = static::createClientWithCredentials()->request('GET', '/activity_responsibles?activity.camp=/camps/'.$camp->getId());
        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains([
            'totalItems' => 2,
            '_links' => [
                'items' => [],
            ],
            '_embedded' => [
                'items' => [],
            ],
        ]);
        $this->assertEqualsCanonicalizing([
            ['href' => $this->getIriFor('activityResponsible1')],
            ['href' => $this->getIriFor('activityResponsible2')],
        ], $response->toArray()['_links']['items']);
    }

    public function testListActivityResponsiblesFilteredByCampIsDeniedForUnrelatedUser() {
        $camp = static::$fixtures['camp1'];
        $response = static::createClientWithCredentials(['username' => static::$fixtures['user4unrelated']->username])
            ->request('GET', '/activity_responsibles?activity.camp=/camps/'.$camp->getId())
        ;

        $this->assertResponseStatusCodeSame(200);

        $this->assertJsonContains(['totalItems' => 0]);
        $this->assertStringNotContainsString($this->getIriFor('activityResponsible1'), $response->getContent());
    }

    public function testListActivityResponsiblesFilteredByPrototypeCampIsAllowedForUnrelatedUser() {
        $camp = static::$fixtures['campPrototype'];
        $response = static::createClientWithCredentials()->request('GET', '/activity_responsibles?activity.camp=/camps/'.$camp->getId());

        $this->assertResponseStatusCodeSame(200);

        $this->assertJsonContains(['totalItems' => 1]);
        $this->assertEqualsCanonicalizing([
            ['href' => $this->getIriFor('activityResponsible1campPrototype')],
        ], $response->toArray()['_links']['items']);
    }
}
