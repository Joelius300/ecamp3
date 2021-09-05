<?php

namespace App\Tests\Api\CampCollaborations;

use App\Tests\Api\ECampApiTestCase;

/**
 * @internal
 */
class ListCampCollaborationsTest extends ECampApiTestCase {
    // TODO security tests when not logged in or not collaborator

    public function testListCampCollaborationsIsAllowedForCollaborator() {
        $response = static::createClientWithCredentials()->request('GET', '/camp_collaborations');
        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains([
            'totalItems' => 6,
            '_links' => [
                'items' => [],
            ],
            '_embedded' => [
                'items' => [],
            ],
        ]);
        $this->assertEqualsCanonicalizing([
            ['href' => $this->getIriFor('campCollaboration1manager')],
            ['href' => $this->getIriFor('campCollaboration1member')],
            ['href' => $this->getIriFor('campCollaboration1guest')],
            ['href' => $this->getIriFor('campCollaboration1camp2')],
            ['href' => $this->getIriFor('campCollaboration1invited')],
            ['href' => $this->getIriFor('campCollaboration2invitedCampUnrelated')],
        ], $response->toArray()['_links']['items']);
    }

    public function testListCampCollaborationsFilteredByCampIsAllowedForCollaborator() {
        $camp = static::$fixtures['camp1'];
        $response = static::createClientWithCredentials()->request('GET', '/camp_collaborations?camp=/camps/'.$camp->getId());
        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains([
            'totalItems' => 4,
            '_links' => [
                'items' => [],
            ],
            '_embedded' => [
                'items' => [],
            ],
        ]);
        $this->assertEqualsCanonicalizing([
            ['href' => $this->getIriFor('campCollaboration1manager')],
            ['href' => $this->getIriFor('campCollaboration1member')],
            ['href' => $this->getIriFor('campCollaboration1guest')],
            ['href' => $this->getIriFor('campCollaboration1invited')],
        ], $response->toArray()['_links']['items']);
    }
}
