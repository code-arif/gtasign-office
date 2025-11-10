<div style="font-family: Helvetica,Arial,sans-serif;min-width:1000px;overflow:auto;line-height:2">
    <div style="margin:50px auto;width:70%;padding:20px 0">
        <div style="border-bottom:1px solid #eee">

        </div>
        <p style="font-size:1.1em">Hi, {{ $user->name }}</p>
        <p>Thank you for joining <strong>Brixl</strong>.
        </p>

        <p>
            To complete your sign-up procedure. please use the One-Time Password (OTP) below:

        </p>
        <h2>

            {{ $otp }}
        </h2>
        <p style="font-size:0.9em;">This code is valid for 5 min </p>
        <p style="font-size:0.9em;">Please do not share this code with anyone.</p> <br>

        <p style="font-size:0.9em;">If you have any questions, feel free to reach us at
            <a href="mailto:support@brixl-backend.site"
                style="color:#00466a;text-decoration:none;">
                support@brixl-backend.site
            </a>.
        </p>


        <hr style="border:none;border-top:1px solid #eee" />

        <p style="font-size:0.9em;">Warm regards,<br />Team Brixl</p>
        <div style="float:right;padding:8px 0;color:#aaa;font-size:0.8em;line-height:1;font-weight:300">

        </div>
    </div>
</div>
