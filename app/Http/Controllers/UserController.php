<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    //
    public function store(Request $request)
    {
        $this->validate($request,[
            'name'=>'required|max:255',
            'email'=>'required|unique:users|max:255',
            'password'=>'required|confirmed|max:255'
        ]);
        $user = new User($request->all());
        $user->api_token = str_random(60);
        $user->save();
        return $user;
    }

    public function login(Request $request)
    {
        $dados = $request->only('email','password');
        $user = User::where('email', $dados['email'])
        ->where('password', $dados['password'])
        ->first();
        $user->api_token = str_random(60);
        $user->update();
        return ['api_token'=>$user->api_token];
    }

    public function update(Request $request, $id)
    {
        $dadosValidacao = [
            'name'=>'required|max:255',
            'email'=>'required|unique:users|max:255'
        ];
        $atualizarPassword = isset($request->all()['password']);

        if($atualizarPassword){
            $dadosValidacao['password'] = 'required|confirmed|max:255';
        }

        $this->validate($request, $dadosValidacao);
        $user = User::find($id);
        $user->name = $request->input('name');
        $user->email = $request->input('email');
        if($atualizarPassword){
            $user->password = $request->input('password');
        }
        $user->update();
        return $user;
    }

    public function view($id)
    {
        return User::find($id);
    }

    public function delete($id)
    {
        if(User::destroy($id)){
            return new Response('Removido com sucesso!', 200);
        }else{
            return new Response('Erro ao remover!', 401);
        }
    }

    public function list()
    {
        return User::all();
    }
}
