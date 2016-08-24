curl https://www.cloudflare.com/api_json.html \
  -d 'a=rec_new' \
  -d 'tkn=e373091e2d730a6473d1b0014859defaafb39' \
  -d 'email=dimitry.lukin@gmail.com' \
  -d 'z=piton.ru' \
  -d 'type=A' \
  -d 'name=sub' \
  -d 'content=1.2.3.4' \
  -d 'ttl=120'
