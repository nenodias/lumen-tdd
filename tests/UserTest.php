<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class UserTest extends TestCase
{
    use DatabaseTransactions;//Faz rollback do teste

    public $dados = [];
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->dados = [
            'name' => 'Nome 02'.date('Ymdis').' '.rand(1, 100),
            'email' => 'email2'.date('Ymdis').' '.rand(1, 100).'@exemplo.com',
            'password' => '123',
            'password_confirmation' => '123'
        ];
    }
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testCreateUser()
    {
        $dados = $this->dados;
        $this->post('/api/user/', $dados);
        $this->assertResponseOk();
        
        $resposta = (array) json_decode($this->response->content());
        $this->assertArrayHasKey('name', $resposta);
        $this->assertArrayHasKey('email', $resposta);
        $this->assertArrayHasKey('id', $resposta);

        $this->seeInDatabase('users',[
            'name'=> $dados['name'],
            'email'=> $dados['email']
        ]);
    }

    public function testLogin(){
        $dados = $this->dados;
        $this->post('/api/user/', $dados);
        $this->assertResponseOk();
        
        $this->post('/api/login/', $dados);
        $this->assertResponseOk();

        $resposta = (array) json_decode($this->response->content());
        $this->assertArrayHasKey('api_token', $resposta);

    }

    public function testViewUser()
    {
        $user = \App\User::first();
        $this->get('/api/user/'.$user->id);
        $this->assertResponseOk();

        $resposta = (array) json_decode($this->response->content());
        $this->assertArrayHasKey('name', $resposta);
        $this->assertArrayHasKey('email', $resposta);
        $this->assertArrayHasKey('id', $resposta);
    }

    public function testAllUser()
    {
        $this->get('/api/users/');
        $this->assertResponseOk();  
        $this->seeJsonStructure([
            '*' => [
                'id',
                'name',
                'email'
            ]
        ]);
    }

    public function testDeleteUser()
    {
        $user = \App\User::first();
        $this->delete('/api/user/'.$user->id);
        $this->assertResponseOk();
        $this->assertEquals("Removido com sucesso!",$this->response->content());
    }

    public function testUpdateUserNoPassword()
    {
        $user = \App\User::first();
        $dados = [
            'name' => 'Nome 02'.date('Ymdis').' '.rand(1, 100),
            'email' => 'email2'.date('Ymdis').' '.rand(1, 100).'@exemplo.com',
        ];
        $this->put('/api/user/'.$user->id, $dados);
        $this->assertResponseOk();

        $resposta = (array) json_decode($this->response->content());

        $this->assertArrayHasKey('name', $resposta);
        $this->assertArrayHasKey('email', $resposta);
        $this->assertArrayHasKey('id', $resposta);

        $this->notSeeInDatabase('users',[
            'name'=> $user->name,
            'email'=> $user->email,
            'id'=> $user->id
        ]);
    }

    public function testUpdateUserWithPassword()
    {
        $user = \App\User::first();
        $dados = [
            'name' => 'Nome 02'.date('Ymdis').' '.rand(1, 100),
            'email' => 'email2'.date('Ymdis').' '.rand(1, 100).'@exemplo.com',
            'password' => '1234',
            'password_confirmation' => '1234'
        ];
        $this->put('/api/user/'.$user->id, $dados);
        $this->assertResponseOk();

        $resposta = (array) json_decode($this->response->content());

        $this->assertArrayHasKey('name', $resposta);
        $this->assertArrayHasKey('email', $resposta);
        $this->assertArrayHasKey('id', $resposta);

        $this->notSeeInDatabase('users',[
            'name'=> $user->name,
            'email'=> $user->email,
            'id'=> $user->id
        ]);
    }
}
