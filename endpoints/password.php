<?php

function api_password_lost($request) {
  $login = $request["login"];
  $url = $request["url"];

  if(empty($login)) {
    $response = new WP_Error("error", "enter your email or login", ["status" => 406]);
    return rest_ensure_response($response);
  }

  $user = get_user_by("email", $login);
  if(empty($user)) {
    $user = get_user_by("login", $login);
  }
  if(empty($user)) {
    $response = new WP_Error("error", "this user doesn't exist", ["status" => 401]);
    return rest_ensure_response($response);
  }

  $user_login = $user->user_login;
  $user_email = $user->user_email;
  $key = get_password_reset_key($user);

  $message = "just press the button below to create a new password :) \r\n";
  $url = esc_url_raw($url . "/?key=$key&login=" . rawurlencode($user_login) . "\r\n");
  // o esc_url_raw garante que o link gerado seja o correto,
  // pois caso seja utilizado algum caractere especial ele formata para um url válido
  $body = $message . $url;

  wp_mail($user_email, "Password Reset", $body);

  return rest_ensure_response("email sent");
}

function register_api_password_lost() {
  register_rest_route("api", "/password/lost", [
    "methods" => WP_REST_Server::CREATABLE,
    "callback" => "api_password_lost",
  ]);
}
add_action("rest_api_init", "register_api_password_lost");

// Password Reset

// function api_password_reset($request) {
//   $login = $request["login"];
//   $password = $request["password"];
//   $key = $request["key"];
//   $user = get_user_by("login", $login);

//   if(empty($user)) {
//     $response = new WP_Error("error", "this user doesn't exist", ["status" => 401]);
//     return rest_ensure_response($response);
//   }

//   $check_key = check_password_reset_key($key, $login);

//   if(is_wp_error($check_key)) {
//     $response = new WP_Error("error", "the token has expired", ["status" => 401]);
//     return rest_ensure_response($response);
//   }

//   reset_password($user, $password);

//   return rest_ensure_response("password has been changed");
// }

// function register_api_password_reset() {
//   register_rest_route("api", "/password/reset", [
//     "methods" => WP_REST_Server::CREATABLE,
//     "callback" => "api_password_reset",
//   ]);
// }
// add_action("rest_api_init", "register_api_password_reset");

// ?>