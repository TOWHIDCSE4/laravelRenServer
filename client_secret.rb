require 'jwt'

key_file = 'key.txt'
team_id = '924H7FJK63' 
client_id = 'com.ikitech.rencity.login' 
key_id = 'MIGTAgEAMBMGByqGSM49AgEGCCqGSM49AwEHBHkwdwIBAQQgNVAnCdkIX5BTdIm9
f3aF5XhQqBwcdiKg7BpF6/YbFdagCgYIKoZIzj0DAQehRANCAARbPYJf9e/Omeyt
565K5j4dfPtGYJ47Msp0LtvwvNNBHcojAYGnOXh93XHhsggKwma+rVV6JmQVSeZa
yy788r2G'

ecdsa_key = OpenSSL::PKey::EC.new IO.read key_file

headers = {
'kid' => key_id
}

claims = {
    'iss' => team_id,
    'iat' => Time.now.to_i,
    'exp' => Time.now.to_i + 86400*180,
    'aud' => 'https://appleid.apple.com',
    'sub' => client_id,
}

token = JWT.encode claims, ecdsa_key, 'ES256', headers

puts token