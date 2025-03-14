<?php

namespace App\Tests\Api\ContentNodes\Storyboard;

use App\Tests\Api\ContentNodes\UpdateContentNodeTestCase;

/**
 * @internal
 */
class UpdateStoryboardTest extends UpdateContentNodeTestCase {
    public function setUp(): void {
        parent::setUp();

        $this->endpoint = '/content_node/storyboards';
        $this->defaultEntity = static::$fixtures['storyboard1'];
    }

    public function testPatchStoryboardAddSection() {
        $response = $this->patch($this->defaultEntity, ['data' => [
            'sections' => [
                'f5ee1e2a-af0a-4fa5-8f3f-b869ed184c5c' => [
                    'column1' => 'A',
                    'column2' => 'B',
                    'column3' => 'C',
                    'position' => 99,
                ],
            ],
        ]]);

        $responseArray = $response->toArray();

        $this->assertResponseStatusCodeSame(200);
        $this->assertArrayHasKey('ab9740f6-61a4-4cae-b574-a73aeb7c5ea0', $responseArray['data']['sections']);
        $this->assertArrayHasKey('cb26d76a-9e3b-43f0-a7c4-0f2ad0ea8029', $responseArray['data']['sections']);
        $this->assertArrayHasKey('f5ee1e2a-af0a-4fa5-8f3f-b869ed184c5c', $responseArray['data']['sections']);

        $this->assertEquals([
            'column1' => 'A',
            'column2' => 'B',
            'column3' => 'C',
            'position' => 99,
        ], $responseArray['data']['sections']['f5ee1e2a-af0a-4fa5-8f3f-b869ed184c5c']);
    }

    public function testPatchStoryboardModifySection() {
        $response = $this->patch($this->defaultEntity, ['data' => [
            'sections' => [
                'ab9740f6-61a4-4cae-b574-a73aeb7c5ea0' => [
                    'column1' => ' testText<script>alert(1)</script>',
                    'position' => 50,
                ],
            ],
        ]]);

        $responseArray = $response->toArray();

        $this->assertResponseStatusCodeSame(200);
        $this->assertArrayHasKey('ab9740f6-61a4-4cae-b574-a73aeb7c5ea0', $responseArray['data']['sections']);
        $this->assertArrayHasKey('cb26d76a-9e3b-43f0-a7c4-0f2ad0ea8029', $responseArray['data']['sections']);

        $this->assertEquals([
            'column1' => ' testText',
            'column2' => $this->defaultEntity->data['sections']['ab9740f6-61a4-4cae-b574-a73aeb7c5ea0']['column2'],
            'column3' => $this->defaultEntity->data['sections']['ab9740f6-61a4-4cae-b574-a73aeb7c5ea0']['column3'],
            'position' => 50,
        ], $responseArray['data']['sections']['ab9740f6-61a4-4cae-b574-a73aeb7c5ea0']);
    }

    public function testPatchStoryboardRemoveSection() {
        $response = $this->patch($this->defaultEntity, ['data' => [
            'sections' => [
                'ab9740f6-61a4-4cae-b574-a73aeb7c5ea0' => null,
            ],
        ]]);

        $responseArray = $response->toArray();

        $this->assertResponseStatusCodeSame(200);
        $this->assertArrayNotHasKey('ab9740f6-61a4-4cae-b574-a73aeb7c5ea0', $responseArray['data']['sections']);
        $this->assertArrayHasKey('cb26d76a-9e3b-43f0-a7c4-0f2ad0ea8029', $responseArray['data']['sections']);
    }

    public function testPatchStoryboardRejectsInvalidJson() {
        $response = $this->patch($this->defaultEntity, ['data' => [
            'sections' => [
                'f5ee1e2a-af0a-4fa5-8f3f-b869ed184c5c' => [
                    'additionalProperty' => 'dummy',
                ],
            ],
        ]]);

        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonSchemaError($response, 'data');
    }
}
