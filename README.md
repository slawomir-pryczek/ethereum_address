# ethereum_address
Create ethereum based addresses, without additional dependencies

Ethereum addresses are based on secp256k1 elliptic curve. The curve has 3 elements:
  - X/Y elements which are the curve's public key and D which is curve's private key.
  - D element, the curve's private key.

Ethereum's private key is hex-encoded secp256k1 D element. While wallet address is derieved by takiking the last 20 bytes of Keccak checksum of the corresponding curve X and Y elements and prefixing it by "0x". X and Y need to be padded to 32 bytes. The full algorithm can be written in pseudo code

1. Take PK and convert it to number D
2. Initialize secp256k1 curve with D
3. Read X, Y elements from the curve, zero-pad them to 32 bytes on the left side
4. Concat X, Y into XY
5. Compute Keccak(xy), take the last 20 bytes as hex
6. Prepend 0x

Provided short code will generate ethereum wallet addresses and convert ethereum's PK to address without additional dependencies. OpenSSL and GMP extensions required.

<pre>// Generate new ethereum wallet (address and private key)
$wallet = ethereum_address::generateAddress();

// retrieve address from ethereum's private key
$address = ethereum_address::fromPK($wallet['pk']);</pre>
