<!DOCTYPE html>
<html lang="en" >
<head>
  <meta charset="UTF-8">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="app-url" content="{{ getBaseURL() }}">
  <meta name="file-base-url" content="{{ getFileBaseURL() }}">

  <title>@yield('meta_title', get_setting('website_name').' | '.get_setting('site_motto'))</title>

  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="index, follow">
  <meta name="description" content="@yield('meta_description', get_setting('meta_description') )" />
  <meta name="keywords" content="@yield('meta_keywords', get_setting('meta_keywords') )">

  @yield('meta')
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/5.0.0/normalize.min.css">

  <style>
    html { background-color: #36383f; font-size: 20px; }

    :root { --hole-distance: 25px; }

    #hole1 { top: var(--hole-distance); left: var(--hole-distance);}
    #hole2 { top: var(--hole-distance); right: var(--hole-distance);}
    #hole3 { bottom: var(--hole-distance); left: var(--hole-distance);}
    #hole4 { bottom: var(--hole-distance); right: var(--hole-distance);}

    .hole {
      width: 20px;
      height: 20px;
      position: absolute;
      border-radius: 50%;
      background-image: radial-gradient(circle at 99%, #f4f4f4 10%, grey 70%);
      transform: rotate(45deg);
    }

    #sign-wrapper {
      background-color: #f4f4f4;
      position: relative;
      width: 80%;
      min-width: 340px;
      max-width: 800px;
      height: 90%;
      margin: 2% auto;
      padding: 50px;
      border: 1px solid #e9ecf0;
      border-radius: 45px;
      box-shadow: 5px 5px 10px #000;
      font-family: "Montserrat", sans-serif;
      font-size: 1rem;
    }

    #header {
      background-color: #ef5350;
      padding: 20px;
      border-radius: 30px 30px 0 0;
      text-align: center;
    }

    h1 {
      text-transform: uppercase;
      color: #f4f4f4;
      font-size: 5.5em;
      line-height: .9em;
      letter-spacing: 3px;
      margin: 0;
      font-weight: 900;
    }

    .strike {
      position: absolute;
      width: 25%;
      height: 10px;
      background-color: #fff;
    }
    #strike1 { top: 115px; left: 80px; }
    #strike2 { top: 115px; right: 80px; }

    #sign-body {
      display: flex;
      flex-wrap: nowrap;
    }
    #copy-container   { flex-basis: 60%; }
    #circle-container { flex-basis: 40%; }

    h2, p {
      text-align: center;
      color: #1d1e22;
    }

    h2 {
      font-size: 3em;
      text-transform: uppercase;
      margin: 40px 0;
      line-height: .9em;
    }

    #copy-container h3{
      font-size: 2.4em;
      text-transform: uppercase;
      margin: 40px 0 0 0;
      line-height: 1.4;
      text-align: center;
    }
    #copy-container p{
      line-height: 1.4em;
      text-align: center;
    }
    p { 
      font-size: 20px; 
    }

    @media screen and (max-width: 930px) {
      #sign-wrapper { 
        font-size: .75rem; }
    }
    @media screen and (max-width: 750px) {
      #sign-wrapper { 
        font-size: .6rem; }
      h2 { 
        margin: 25px 0; }
      .strike { 
        visibility: hidden; }
    }
    @media screen and (max-width: 600px) {
      #sign-wrapper { 
        font-size: .4rem; 
        padding: 15px;
        border-radius: 25px;
      }
      #header {
        border-radius: 20px 20px 0 0;
      }
      #circle-container {
        width: 50%;
      }
      .hole {
        width: 10px;
        height: 10px;
      }
      :root {
        --hole-distance: 8px;
      }
    }
    @media screen and (max-width: 450px) {
      #sign-wrapper { 
        font-size: .34rem; }
      h2 { 
        margin: 10px; }
      p { 
        font-size: 14px; }
    }
  </style>

</head>
<body>
<!-- partial:index.partial.html -->
<link href="https://fonts.googleapis.com/css?family=Montserrat:500,700,900" rel="stylesheet">

<div id="sign-wrapper">
  <div id="hole1" class="hole"></div>
  <div id="hole2" class="hole"></div>
  <div id="hole3" class="hole"></div>
  <div id="hole4" class="hole"></div>
  <header id="header">
    <h1>403 forbidden</h1>
    <div id="strike1" class="strike"></div>
    <div id="strike2" class="strike"></div>
  </header>
  <section id="sign-body">
    <div id="copy-container">
      <h3>Your IP has been banned</h3>
      <p><strong>IP Blocked: </strong>. Your IP has been flagged & is currently banned from viewing this site. To remove the ban, please contact support center or call <strong>{{ @get_setting('helpline_number') }}</strong></p>
      <p>Your IP Address Is: <strong>{{ @request()->ip() }}</strong></p>
    </div>
    <div id="circle-container">
      <svg version="1.1" viewBox="0 0 500 500" preserveAspectRatio="xMinYMin meet">
        <defs>
          <pattern id="image" patternUnits="userSpaceOnUse" height="450" width="450">
            <image x="25" y="25" height="450" width="450" xlink:href="https://upload.wikimedia.org/wikipedia/commons/8/89/Portrait_Placeholder.png"></image>
          </pattern>
        </defs>
        <circle cx="250" cy="250" r="200" stroke-width="40px" stroke="#ef5350" fill="url(#image)"/>
        <line x1="100" y1="100" x2="400" y2="400" stroke-width="40px" stroke="#ef5350"/>
      </svg>
    </div>
  </section>
</div>
<!-- partial -->
  
</body>
</html>
