<?php

use Symfony\Component\HttpKernel\Exception\HttpException;
use Weeks\Laravel\Repositories\BaseEloquentRepository;
use Weeks\Laravel\Repositories\Traits\ThrowsHttpExceptions;

class ThrowsHttpExceptionsTraitTest extends BaseTestCase
{
    /**
     * @var HttpRepository
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
        $this->repo = new HttpRepository();
    }

    /**
     * @test
     */
    public function it_throws_exception_when_item_not_found_by_id()
    {
        try {
            $this->repo->getById(100);
        } catch (HttpException $e) {
            $this->assertEquals(HttpException::class, get_class($e));

            return;
        }

        $this->fail('Expected Exception is not thrown');
    }

    /**
     * @test
     */
    public function it_throws_exception_when_item_not_found_by_column()
    {
        try {
            $this->repo->getItemByColumn('jimmy', 'name');
        } catch (HttpException $e) {
            $this->assertEquals(HttpException::class, get_class($e));

            return;
        }

        $this->fail('Expected Exception is not thrown');
    }


    /**
     * @test
     */
    public function it_only_throws_exception_for_specific_methods()
    {
        $this->repo->getAll();
        $this->repo->getPaginated();
        $this->repo->getForSelect('name');
        $this->repo->getCollectionByColumn('name');
        $this->repo->testNotThrow();
    }

    /**
    * @test
    *
    */
    public function it_throws_exceptions_for_custom_methods()
    {

        try {
            $this->repo->testThrow();
        } catch (HttpException $e) {
            $this->assertEquals(HttpException::class, get_class($e));

            return;
        }

        $this->fail('Expected Exception is not thrown');
    }
}

class HttpRepository extends BaseEloquentRepository
{
    use ThrowsHttpExceptions;

    protected $throwableMethods = ['testThrow'];

    public function __construct()
    {
        $this->model = new Post();
        $this->setUses();
    }

    public function testThrow()
    {
        return $this->doQuery(function(){
            return null;
        });
    }

    public function testNotThrow()
    {
        return $this->doQuery(function(){
            return null;
        });
    }
}