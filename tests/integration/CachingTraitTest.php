<?php

use Weeks\Laravel\Repositories\BaseEloquentRepository;
use Weeks\Laravel\Repositories\Traits\CacheResults;

class CachingTraitTest extends BaseTestCase
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
            '--realpath' => realpath(__DIR__ . '/../migrations'),
        ]);
        $this->withFactories(realpath(__DIR__ . '/../factories'));
        $this->repo = new CachingRepository();
    }

    /**
     * @test
     */
    public function default_ttl_can_be_overridden()
    {
        $this->assertEquals(30, $this->invokeMethod($this->repo, 'getCacheTtl'));
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
    public function it_caches_the_request_with_arguments()
    {
        factory(Post::class)->create(['title' => 'so say we all']);

        $this->repo->getItemByColumn('so say we all', 'title');
        $before = count(\DB::getQueryLog());

        $this->repo->getItemByColumn('so say we all', 'title');

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

    /**
    * @test
    */
    public function it_caches_custom_methods()
    {
        factory(Post::class)->create();
        $this->repo->getTestMethod();
        $before = count(\DB::getQueryLog());

        $this->repo->getTestMethod();

        $this->assertEquals($before, count(\DB::getQueryLog()), 'The database was hit unexpectedly.');
    }

    /**
    * @test
    */
    public function caching_can_be_disabled_programatically()
    {
        factory(Post::class)->create();
        $this->repo->getAll();
        $before = count(\DB::getQueryLog());

        $this->repo->disableCaching()->getAll();

        $this->assertEquals($before + 1, count(\DB::getQueryLog()), 'The database was hit unexpectedly.');
    }
}

class CachingRepository extends BaseEloquentRepository
{
    use CacheResults;

    protected $cacheTtl = 30;
    protected $nonCacheableMethods = ['getById'];
    protected $cacheableMethods = ['getTestMethod'];

    public function __construct()
    {
        $this->model = new Post();
        $this->setUses();
    }

    public function getTestMethod()
    {
        return $this->doQuery(function() {
            return $this->model->where('title', 'some title')->get();
        });
    }
}