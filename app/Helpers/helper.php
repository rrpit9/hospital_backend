<?php

    use App\Models\User;
    use Illuminate\Support\Str;

    if (! function_exists('successResponse'))
    {
        function successResponse($msg = '', $data = null, $status = 200)
        {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'data' => $data
            ], $status);
        }
    }

    if (! function_exists('errorResponse'))
    {
        function errorResponse($msg = '', $data = null, $status = 422)
        {
            return response()->json([
                'success' => false,
                'message' => $msg,
                'data' => $data
            ], $status);
        }
    }

    // Encrypt Or Decrypt
    if (! function_exists('encrypt_decrypt'))
    {
        function encrypt_decrypt($string, $action = 'encrypt')
        {
            $encrypt_method = "AES-256-CBC";
            $encryption_key = config('app.encryption_key'); // Encryption key
            $encryption_iv = config('app.encryption_iv'); // Encryption Iv
            $key = hash('sha256', $encryption_key);
            $iv = substr(hash('sha256', $encryption_iv), 0, 16); // sha256 is hash_hmac_algo
            if ($action == 'encrypt') {
                $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
                $output = base64_encode($output);
            } else if ($action == 'decrypt') {
                $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
            }
            return $output;
        }
    }

    // File Upload System
    if (! function_exists('fileUpload'))
    {
        function fileUpload($image, $folder = 'image')
        {
            try{
                $random = randomGenerator();
                $imageurl = $random . '.' . $image->extension();
    
                $image->move(public_path('uploads/'.$folder), $imageurl);
                $imageurl = 'uploads/'.$folder.'/'.$imageurl;
            }catch(Exception $e){
                log_exception($e);
                return null;
            }
            return $imageurl;
        }
    }
    
    // Random Unique Number Generator
    if (! function_exists('randomGenerator'))
    {
        function randomGenerator()
        {
            return uniqid() . '' . date('ymdhis') . '' . uniqid();
        }
    }

    // Generate Unique Random AlphaNumeric of Defined Length
    if (! function_exists('generateUniqueAlphaNumeric'))
    {
        function generateUniqueAlphaNumeric($length = 10)
        {
            $random_string = '';
            for ($i = 0; $i < $length; $i++) {
                $number = random_int(0, 36);
                $character = base_convert($number, 10, 36);
                $random_string .= $character;
            }
            return $random_string;
        }
    }

    // String Verifiy For DB Check
    if (! function_exists('strCheck'))
    {
        function strCheck(string $string = "")
        {
            $returnString = "";
            for ($i = 0; $i < strlen($string); $i++) {
                if ($string[$i] == '"') {
                    $returnString .= '&#34;';
                } else if ($string[$i] == "'") {
                    $returnString .= '&#39;';
                } else {
                    $returnString .= $string[$i];
                }
            }
            return $returnString;
        }
    }

    if (! function_exists('dateCheck'))
    {
        function dateCheck($date = "", $timeToShow = false, $format = 'd M, Y')
        {
            if($timeToShow){$format.=' h:i A';}
            return ($date ? date($format, strtotime($date)) : null);
        }
    }

    // Money Format
    if (! function_exists('moneyFormat'))
    {
        function moneyFormat($amount)
        {
            $amount = number_format($amount,2);
            $amount = preg_replace("/(\d+?)(?=(\d\d)+(\d)(?!\d))(\.\d+)?/i", "$1,", $amount);
            return $amount;
        }
    }
	
    if (! function_exists('log_exception'))
    {
        function log_exception(Throwable $exception)
        {
            \Log::error($exception);
        }
    }

    if (! function_exists('studly_case'))
    {
        /*Convert a value to studly caps case.*/
        function studly_case($value)
        {
            return Str::studly($value);
        }
    }

    if (! function_exists('str_singular'))
    {
        /*Get the singular form of an English word.*/
        function str_singular($value)
        {
            return Str::singular($value);
        }
    }

    