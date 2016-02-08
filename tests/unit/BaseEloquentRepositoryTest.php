<?php

use Mockery as m;
use PHPUnit_Framework_Assert as reader;
use Weeks\Laravel\Repositories\BaseEloquentRepository;

class BaseEloquentRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BaseEloquentRepository
     */
    protected $repo;

    public function setUp()
    {
        $this->repo = new RepoStub();
    }

    public function tearDown()
    {
        m::close();
    }

    /**
     * @test
     */
    public function it_is_an_instance_of_the_base_repo_class()
    {
        $this->assertInstanceOf('Weeks\Laravel\Repositories\BaseEloquentRepository', $this->repo);
    }

    /**
     * @test
     */
    public function it_implements_the_contract()
    {
        $this->assertInstanceOf('Weeks\Laravel\Repositories\RepositoryContract', $this->repo);
    }

    /**
     * @test
     */
    public function it_does_not_eagerly_include_relationships()
    {
        $this->assertEquals([], reader::readAttribute($this->repo, 'requiredRelationships'));
    }

    /**
     * @test
     */
    public function it_loads_all_relationships_with_the_all_keyword()
    {
        $this->repo->with('all');
        $this->assertEquals(['author', 'comments'], reader::readAttribute($this->repo, 'requiredRelationships'));
    }

    /**
     * @test
     */
    public function it_sets_a_single_relationship_when_a_string_is_given()
    {
        $this->repo->with('author');
        $this->assertEquals(['author'], reader::readAttribute($this->repo, 'requiredRelationships'));
    }

    /**
     * @test
     */
    public function it_sets_allowed_relationships_and_ignores_the_rest()
    {
        $this->repo->with(['author', 'comments', 'notAllowed']);
        $this->assertEquals(['author', 'comments'], reader::readAttribute($this->repo, 'requiredRelationships'));
    }

    /**
     * @test
     */
    public function it_applies_the_requested_relationship_to_the_query()
    {
        /** @var ModelStub $model */
        $model = reader::readAttribute($this->repo, 'model');
        $this->repo->with('all')->getAll();

        $this->assertEquals(['author', 'comments'], $model->getRequestedRelationships());
    }

}

class RepoStub extends BaseEloquentRepository
{
    protected $model = 'TestModel';
    protected $relationships = ['author', 'comments'];

    public function __construct()
    {
        $this->model = new ModelStub();
    }
}

class ModelStub
{
    protected $requestedRelationships;

    public function orderBy()
    {
        return $this;
    }

    public function with(array $requestedRelationships = [])
    {
        $this->requestedRelationships = $requestedRelationships;

        return $this;
    }

    public function get()
    {
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRequestedRelationships()
    {
        return $this->requestedRelationships;
    }
}
