<?php

namespace gaurav\tagging\Test\Unit;

use gaurav\tagging\Test\TestCase;
use gaurav\tagging\Models\Mention;
use gaurav\tagging\Test\TestCommentModel;
use gaurav\tagging\Collections\MentionCollection;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class MentionsTest extends TestCase
{
    /** @test */
    public function can_mention_single_model()
    {
        $mention = $this->testCommentModel->mention($this->testUserModel->first());

        $this->assertTrue($this->testCommentModel->mentions(false)->contains($mention));
    }

    /** @test */
    public function can_mention_many_models()
    {
        $mentions = $this->testCommentModel->mention($this->testUserModel->all());

        $this->assertInstanceOf(MentionCollection::class, $mentions);
    }

    /** @test */
    public function can_mention_encoded_string()
    {
        $encoded = $this->testUserModel->all()->map(function($user) {
            return "users:{$user->id}";
        })->implode(',');

        $mentions = $this->testCommentModel->mention($encoded);

        $this->assertInstanceOf(MentionCollection::class, $mentions);
    }

    /** @test */
    public function can_unmention_single_model()
    {
        $result = $this->testCommentModel->unmention($this->testUserModel->first());

        $this->assertInstanceOf(TestCommentModel::class, $result);
    }

    /** @test */
    public function can_unmention_many_models()
    {
        $result = $this->testCommentModel->unmention($this->testUserModel->all());

        $this->assertInstanceOf(TestCommentModel::class, $result);
    }

    /** @test */
    public function can_unmention_encoded_string()
    {
        $encoded = $this->testUserModel->all()->map(function($user) {
            return "users:{$user->id}";
        })->implode(',');

        $result = $this->testCommentModel->unmention($encoded);

        $this->assertInstanceOf(TestCommentModel::class, $result);
    }

    /** @test */
    public function can_get_mentions_collection_unresolved()
    {
        $mentions = $this->testCommentModel->mentions(false);

        $this->assertInstanceOf(MentionCollection::class, $mentions);
    }

    /** @test */
    public function can_get_mentions_collection_resolved()
    {
        $mentions = $this->testCommentModel->mentions(true);

        $this->assertInstanceOf(MentionCollection::class, $mentions);
    }

    /** @test */
    public function can_notify_new_mention()
    {
        $mention = $this->testCommentModel->mention($this->testUserModel->first());

        $mention->notify();

        $this->assertInstanceOf(Mention::class, $mention);
    }

    /** @test */
    public function can_notify_new_mentions()
    {
        $mentions = $this->testCommentModel->mention($this->testUserModel->all());

        $mentions->notify();

        $this->assertInstanceOf(MentionCollection::class, $mentions);
    }

    /** @test */
    public function can_get_recipient()
    {
        $mention = $this->testCommentModel->mention($this->testUserModel->first());
        $recipient = $mention->recipient();

        $this->assertInstanceOf(get_class($this->testUserModel), $recipient);
    }

    /** @test */
    public function can_get_collection_encoded()
    {
        $this->testCommentModel->mention($this->testUserModel->all());
        $encoded = $this->testCommentModel->mentions()->encoded();

        $this->assertInternalType('string', $encoded);
    }
}
