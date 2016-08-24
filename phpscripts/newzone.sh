curl -X POST "https://api.cloudflare.com/client/v4/zones" \
-H "X-Auth-Email: dimitry.lukin@gmail.com" \
-H "X-Auth-Key: e373091e2d730a6473d1b0014859defaafb39" \
-H "Content-Type: application/json" \
--data '{"name":"chukchi.ru","jump_start":false}'

