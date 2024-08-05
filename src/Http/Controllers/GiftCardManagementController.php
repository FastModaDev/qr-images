<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

use FastModaDev\QrImages\Models\NotificacionGlobal;


class GiftCardManagementController extends Controller
{


  /**
   * Pagination.
  */
  public function pagination( Request $request )
  {

    $show      = isset( $request['show'] ) ? $request['show'] : 10 ;

    if ( isset( $request['sortBy'] ) && count( $request['sortBy'] ) > 0 )
    {
      $order      = $request['sortBy'][0]['key'] ;
      $type_order = $request['sortBy'][0]['order'];
    }
    else{
      $order      = 'id' ;
      $type_order = 'asc' ;
    }

    $users = User::select([
      'users.*',
      'roles.name as role',
      // DB::raw('concat(name , " " , last_name) as fullname')
    ])
    // ->with('roles')
    ->leftJoin('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
    ->leftJoin('roles', 'model_has_roles.role_id', '=', 'roles.id')
    ->orderBy( $order , $type_order );
    
    // $columnas = ['name', 'last_name', 'email' , 'username' ]; 
    $columnas = ['users.name','email' ]; 
    foreach ( $columnas as $columna )
    {
      $users->orWhere( $columna , 'LIKE' , '%' .$request['search'].'%');
    }

    $users = $users->paginate($show);

    foreach ( $users as $key )
    {

      if ( $key->role === null )
      {
        $key->role = 'Sin Rol';
      }
      
    }

    return [
      'pagination' => [

          'total' => $users->total(),
          'current_page' => $users->currentPage(),
          'per_page' => $users->perPage(),
          'last_page' => $users->lastPage(),
          'from' => $users->firstItem(),
          'to' => $users->lastPage(),

      ],
      'users' => $users,
    ];

  }

  /**
   * Store a newly created resource in storage.
   */
  public function store( Request $request )
  {

    $messages = [
      'data.type.required' => 'El campo "Tipo novedad" es requerido.',
      'data.ref.required' => 'El campo "Referencia Externa" es requerido.',
      'data.descripcion.required' => 'El campo "Descripción " es requerido.',
    ];

    $validator = Validator::make( $request->all() , [

      'name' => 'required|max:255',
      'username' => 'required|unique:users|max:255',
      'email' => 'required|unique:users|max:255',
      'password' => 'required|confirmed',
      'role' => 'required',

    ] , $messages );

    if ( $validator->fails() )
    {
      return response()->json([
        'status' => 'error',
        'errors' => $validator->errors(),
      ], 202);
    }

    
    $data = $request->all();
    $data['password'] = Hash::make( $request->password );

    unset( $data['role'] );
    unset( $data['confirm_password'] );

    $user = User::create( $data );

    $role = Role::find( $request->role );
    if ( $role )
    {
      $user->assignRole( $role->name );
    }

    return response()->json([
      'status'  => 'success',
      'msg'     => 'Usuario creado',
      'data'    => $user,
    ]);

  }

  /**
   * Display the specified resource.
   */
  public function show(string $id)
  {
      //
      $user = User::find( $id );

      return Inertia::render('Backoffice/Users/DetailsUser', [
          'title' => 'Editar usuario',
          'user_' => $user,
          'type' => 'edit',
      ]);
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, string $id)
  {

      $request->validate([
          'name' => 'required|max:255',
          'last_name' => 'required|max:255',

          'country' => 'required',
          'state' => 'required',
          'phone' => 'required',
          'address' => 'required',
          // 'address_two' => 'required',
          'type_doc' => 'required',
          'document' => 'required',
      ]);

      // $request->password = Hash::make( '123456' );
      

      $user = User::where( 'id' , '=' , $id )
      ->update( 
          // $request->all() 
          [

              'name'          => $request->name,
              'last_name'     => $request->last_name,
  
              'country'       => $request->country,
              'city'          => $request->city,
              'state'         => $request->state,
              'phone'         => $request->phone,
              'address'       => $request->address,
              'address_two'   => $request->address_two,
              'type_doc'      => $request->type_doc,
              'document'      => $request->document,

              // 'password'      => $request->password
          ]
      );

      if ( $request->role )
      {
          $user = User::find( $id );

          $roles_user = $user->getRoleNames();
          foreach ($roles_user as $key )
          {
              $user->removeRole( $key );
          }
          

          $user->assignRole($request->role);
      }

      return response()->json([
          'state' => 'ok',
          'messages' => 'Usuario creado',
          'data' => $user,
      ]);
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(string $id)
  {
      //
      $user = User::where( 'id' ,  $id )
      ->delete();

      return response()->json([
          'state' => 'ok',
          'messages' => 'Usuario borrado',
          'data' => $user,
      ]);

  }

}
