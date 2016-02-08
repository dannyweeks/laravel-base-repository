<?php

use Weeks\Laravel\Repositories\BaseEloquentRepository;
use Weeks\Laravel\Repositories\CacheResults;

class CachingTraitTest extends Orchestra\Testbench\TestCase
{
    /**
     * @var CachingRepository
     */
    protected $repo;

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('cache.default', 'array');
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    public function setUp()
    {
        parent::setUp();
        \DB::enableQueryLog();
        $this->artisan('migrate', [
            '--database' => 'testbench',
            '--realpath' => realpath(__DIR__.'/../migrations'),
        ]);
        $this->withFactories(realpath(__DIR__.'/../factories'));
        $this->repo = new CachingRepository();
    }

    /**
     * @test
     */
    public function repo_is_aware_it_is_caching()
    {
        $this->assertTrue($this->repo->isCaching());
    }

    /**
    * @test
    */
    public function default_ttl_can_be_overridden()
    {
        $this->assertEquals(30, $this->repo->getCacheTtl());
    }

    /**
    * @test
    */
    public function it_caches_the_request()
    {
        factory(Post::class)->create();

        $this->repo->getAll();
        $before = count(\DB::getQueryLog());

        $this->repo->getAll();

        $this->assertEquals($before, count(\DB::getQueryLog()));

    }

    /**
    * @test
    */
    public function it_ignores_specified_requests()
    {
        factory(Post::class)->create();
        $this->repo->getById(1);
        $before = count(\DB::getQueryLog());

        $this->repo->getById(1);

        $this->assertEquals($before + 1, count(\DB::getQueryLog()));
    }
}

class CachingRepository extends BaseEloquentRepository
{
    use CacheResults;

    protected $cacheTtl = 30;
    protected $ignoredMethods = ['getById'];

    public function __construct()
    {
        $this->model = new Post();
    }
}