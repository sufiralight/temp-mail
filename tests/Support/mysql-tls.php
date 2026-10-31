<?php

// Generate disposable local CA/server certificates, never used by the actual deployment.
$directory = realpath($argv[1]);
if (! $directory || ! str_contains(str_replace('\\', '/', $directory), '/storage/framework/testing/mysql-runtime')) {
    exit(1);
}
$configuration = $directory.'/openssl-test.cnf';
file_put_contents($configuration, "[ req ]\ndistinguished_name = dn\n[ dn ]\n[ ca_ext ]\nbasicConstraints = critical,CA:TRUE\nkeyUsage = critical,keyCertSign,cRLSign\n[ server_ext ]\nbasicConstraints = CA:FALSE\nkeyUsage = digitalSignature,keyEncipherment\nextendedKeyUsage = serverAuth\nsubjectAltName = DNS:localhost,IP:127.0.0.1\n");
$options = ['config' => $configuration, 'private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA, 'digest_alg' => 'sha256'];
$caKey = openssl_pkey_new($options);
$caCsr = openssl_csr_new(['commonName' => 'Temp Mail Disposable Test CA'], $caKey, $options);
$ca = openssl_csr_sign($caCsr, null, $caKey, 2, [...$options, 'x509_extensions' => 'ca_ext']);
$serverKey = openssl_pkey_new($options);
$csr = openssl_csr_new(['commonName' => 'localhost'], $serverKey, $options);
$server = openssl_csr_sign($csr, $ca, $caKey, 2, [...$options, 'x509_extensions' => 'server_ext']);
openssl_x509_export_to_file($ca, $directory.'/test-ca.pem');
openssl_x509_export_to_file($server, $directory.'/test-server.pem');
openssl_pkey_export_to_file($serverKey, $directory.'/test-server-key.pem', null, $options);
echo "Disposable TLS certificate generated.\n";
