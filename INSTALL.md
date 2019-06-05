# theBed - Install instructions

## Installation from scratch

1) You need to have your own web server configured with a webserver capable of running php56 at a minimum.
2) Make sure to install phpXX-mbstrings, phpXX-common, phpXX-dba
3) Wherever your root directory is, just `git clone https://github.com/tuctboh/theBed.git`
4) Make sure the ownership of the files/directories is suitable for your server
5) Go into theBed and `cp theBed.ini.sample theBed.ini`
6) Ensure you have an account and go to `https://developer.amazon.com/alexa/console/ask`
7) Click the blue "Add Skill"
8) Enter a descriptive Skill name, such as "theBedSkill" and the "Create Skill"
9) Choose "Start from Scratch" and continue.
10) Click the "1. Invocation Name" and type something into "Skill Invocation Name". This will be what you say after "Alexa, ask"
11) Click the "JSON Editor" on the left and drag/drop a copy of `theBed/theBed.skill/models/en-US.json` into it
12) On the left, click "Endpoint", then "https", then put the URL to your web server in the "Default Region" (Eg: https://example.com/theBed/theBed.php). Drop down the SSL cert info and mark it appropriately. Let's Encrypt actually will need you to upload the full pem file.
13) Click "Save Endpoints" on the top and hope its ok.
14) Click "Invocation on the left" and then "Save Model" on the top.
15) Click "Build Model" on the top and ensure it completes.
16) Go back to "Your Skills" and click "View Skill ID". Cut the part AFTER the "amzn1.ask.skill", and replace this in the theBed.ini
17) You should be good to go!

## Contributing
Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

## License
GPLv3
