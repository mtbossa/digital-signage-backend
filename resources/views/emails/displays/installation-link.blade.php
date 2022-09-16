@component('mail::message')
  # Link para instalação

  Esse é o link para download do instalador da aplicação:
  <br>
  <{{ $installerUrl }}>

  Para instalar: digite <br>
  **`curl -H "Authorization: Bearer
  <DISPLAY_API_TOKEN>" {{ $installerUrl }} | sudo bash`** <br>
    no terminal do Raspberry

  {{ config('app.name') }}
@endcomponent
