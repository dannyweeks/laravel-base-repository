<?php

use Illuminate\Database\Eloquent\Model as Eloquent;
use Weeks\Laravel\Repositories\BaseEloquentRepository;

class EloquentIntegrationTest extends Orchestra\Testbench\TestCase
{
    /**
     * @var PostRepository
     */
    protected $posts;

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
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
        $this->posts = new PostRepository();
        $this->artisan('migrate', [
            '--database' => 'testbench',
            '--realpath' => realpath(__DIR__.'/../migrations'),
        ]);
        $this->withFactories(realpath(__DIR__.'/../factories'));
    }

    /**
    * @test
    */
    public function it_fetches_all_the_records()
    {
        factory(Post::class, 2)->create();

        $posts = $this->posts->getAll();

        $this->assertEquals(2, $posts->count());
    }

    /**
    * @test
    */
    public function it_paginates_records()
    {
        factory(Post::class, 10)->create();

        $posts = $this->posts->getPaginated(5);

        $this->assertEquals(5, $posts->count());
        $this->assertInstanceOf(\Illuminate\Pagination\AbstractPaginator::class, $posts);
    }

    /**
    * @test
    */
    public function it_formats_records_for_a_select_field()
    {
        factory(Post::class, 4)->create();
        $lastPost = factory(Post::class)->create();

        $posts = $this->posts->getForSelect('title');

        $this->assertEquals(5, count($posts));
        $this->assertArrayHasKey(5, $posts);
        $this->assertEquals($lastPost->title, $posts[5]);
    }

    /**
     * @test
     */
    public function it_fetches_record_by_id()
    {
        $post = factory(Post::class)->create();
        $returnedPost = $this->posts->getById(1);

        $this->assertEquals($post->title, $returnedPost->title);
        $this->assertEquals($post->body, $returnedPost->body);
    }

    /**
    * @test
    */
    public function it_fetches_record_by_column()
    {
        $post = factory(Post::class)->create(['title' => 'a funky title']);
        $returnedPost = $this->posts->getItemByColumn('a funky title', 'title');

        $this->assertEquals($post->title, $returnedPost->title);
    }

    /**
    * @test
    */
    public function it_fetches_collection_by_column()
    {
        $title = 'a super funky title';
        factory(Post::class, 8)->create();
        $requiredPosts = factory(Post::class, 2)->create(['title' => $title]);

        $posts = $this->posts->getCollectionByColumn($title, 'title');

        $this->assertEquals($requiredPosts->first()->id, $posts->first()->id);
    }

    /**
    * @test
    */
    public function it_can_persist_a_record()
    {
        $title = 'a post';
        $body = 'some witty blog post';
        $this->posts->create(['title' => $title, 'body' => $body]);

        $post = Post::find(1);

        $this->assertEquals($post->title, $title);
        $this->assertEquals($post->body, $body);
    }

    /**
    * @test
    */
    public function it_can_delete_a_record()
    {
        $created = factory(Post::class)->create();

        $this->posts->delete($created->id);

        $this->assertFalse(in_array($created->id, Post::all()->pluck('id')->toArray()));
    }
}

class Post extends Eloquent
{
    protected $fillable = ['title', 'body'];
}

class PostRepository extends BaseEloquentRepository
{
    protected $model = Post::class;
}