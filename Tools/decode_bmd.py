import sys

# IGCN BMD XOR key
key = bytes([0xFC, 0xCF, 0xAB])

with open(sys.argv[1], 'rb') as f:
    data = f.read()

decoded = bytearray()
for i, b in enumerate(data):
    decoded.append(b ^ key[i % len(key)])

# Print hex and ascii
for i in range(0, len(decoded), 33):
    chunk = decoded[i:i+33]
    hex_str = ' '.join(f'{b:02x}' for b in chunk)
    ascii_str = ''.join(chr(b) if 32 <= b < 127 else '.' for b in chunk)
    print(f"Record {i//33}: {hex_str}")
    print(f"  ASCII:  {ascii_str}")
    print()
