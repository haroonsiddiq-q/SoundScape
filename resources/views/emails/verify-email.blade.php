<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; margin: 0; padding: 0;}
        .wrapper { width: 100%; padding: 32px 0; max-width: 600px; }
        .header { text-align: center; padding: 25px 0; background-color: #008be6; border-radius: 8px 8px 0px 0px;}
        .header img { max-width: 125px; }
        .content { margin: 0 auto; max-width: 600px; border-radius: 8px; background-color: #f3f4f780;  overflow: hidden; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04); }
        .body-box { padding: 32px; }
        h1 { color: #3d4852; font-size: 19px; font-weight: bold; margin-top: 0; }
        p { font-size: 16px; line-height: 1.5em; margin-top: 0; }
        .button-container { text-align: center; margin: 30px 0; }
        .button { display: inline-block; background-color: #008be6; color: #ffffff; text-decoration: none; padding: 12px 25px; border-radius: 8px; font-weight: bold; }
        .subcopy { border-top: 1px solid #e8e5ef; margin-top: 25px; padding-top: 25px; font-size: 14px; }
        .subcopy p { font-size: 14px; }
        .footer { text-align: center; font-size: 12px; color: #b0adc5;}
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <img src="https://res.cloudinary.com/dtgkeblo9/image/upload/v1772727464/SoundScapeLogoWhite_rzcrwk.png" alt="SoundScape Logo">
        </div>
        
        <div class="content">
            <div class="body-box">
                <h1>Welcome to SoundScape<br>{{ $name }}</h1>
                
                <p>Please click the button below to verify your email address.</p>
                
                <div class="button-container">
                    <a href="{{ $url }}" class="button" style="color: #ffffff !important; text-decoration: none;">
                        <span style="color: #ffffff;">Verify Email Address</span>
                    </a>
                </div>
                
                <p>If you did not create an account, you do not need to take any further action.</p>
                
                <p>Best regards,<br>SoundScape</p>
                
                <div class="subcopy">
                    <p>
                        If you are having trouble clicking the "Verify Email Address" button, please copy and paste the URL below into your web browser: <br>
                        <span style="word-break: break-all; color: #008be6;">{{ $url }}</span>
                    </p>
                </div>
            </div>
        </div>
        
        <div class="footer">
            &copy; {{ date('Y') }} SoundScape. All rights reserved.
        </div>
    </div>
</body>
</html>