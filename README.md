# theBed

This is a utility to be able to control your Sleep Number(r) bed from your Amazon Alexa(r) device.

## Installation

So there are a few ways you can do this..... From least of an issue but biggest security problems to the inverse 

1) I need your Amazon UserId (Not email/password) and your SleepIQ userid and password. You'd be using the global available skill which is the least set up for you.... But now I've got your SleepIQ userid/password. You could ALWAYS change it and within 2 hours I wouldn't be able to access things anymore (Being honest). 

2) There is another project I have called [TheBedPassProxy](https://github.com/tuctboh/TheBedPassProxy). This takes a bit of work (You need to get an Amazon AWS account, install some programs, run some commands). You then give me the output of this and your Amazon UserId (Not email/password). You'll then use the global skill which is already set up, but I don't have your SleepIQ userid and password. As long as you keep that Pass Proxy running, I never see it but can use it to contact the SleepIQ system. That too would time out after 2 hours once you disable it

3) You run your own copy. This requires you creating at a minimum an Amazon Developer Account. You'll need to host your own SSL webserver with PHP capabilties. You'll need to create your own skill and allow it to be accessed personally by your id/devices. This is the biggest amount of work, but the least of a security issue for you.


## Usage

"Alexa, ask THE BED get left" - Will tell you the current setting of the left side

## Contributing
Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

## License
GPLv3
