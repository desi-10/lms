<?php
    declare(strict_types=1);

    namespace App\Traits;

    trait Token
    {
        /**
         * This function is used to create a token for a user
         * @param int $user_id The id of the user
         * @param string $username The username of the user
         * @return string An array of the token
         */
        private static function generateToken(int $user_id) :string{
            $token = (string) $user_id;
            $token .= ".".uniqid();

            return static::encode($token);
        }

        /**
         * This function is used to encode a token data
         * @param array|string $data The data to be encoded
         * @return string A base64 encoded string
         */
        private static function encode($data) :string{
            // return base64_encode(json_encode(["data" => $data]));
            return base64_encode($data);
        }

        /**
         * This function is used to decode a token data
         * @param string $encoded_data The encoded data to be decoded
         * @return mixed The decoded data
         */
        private static function decode(string $encoded_data){
            // $decoded = (array) json_decode(base64_decode($encoded_data));
            $decoded = base64_decode($encoded_data);

            //separate unique id from the real data
            /*$decoded = explode(".",$decoded);
            $decoded = [
                "user_id" => $decoded[0], "uniqueid" => $decoded[1]
            ];*/

            return $decoded;
        }
    }