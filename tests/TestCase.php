<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // The SEO assertions only mean anything on an indexable site: with
        // this false every page correctly emits noindex and the sitemap and
        // robots rules are untestable. Tests that specifically cover the
        // staging behaviour set it back to false themselves.
        config()->set('site.indexable', true);
    }
}
