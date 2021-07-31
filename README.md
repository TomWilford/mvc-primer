# mvc-primer

## What's this then?
Just what everyone wanted, a new  PHP Framework! Well, sort of, but not really. 

It's the framework from Pro PHP MVC by Chris Pitt (2012) but a bit modernised. And it works! There's a Vagrant file & accompanying set of commands to get the framework up and running (LAMP on CentOs/7). That's it really!

### "A bit modernised"??
* Autoloading          > _Replaced with Composer._
* MySQLi               > _Replaced with PDO._
* Deprecated functions > _Replaced with non-deprecated alternatives._
* General formatting   > _Generally better. It's (mostly) PSR-2!_
* No encryption        > _BCRYPT & rehashing._

### Anything else? 
Yes!
* I got a little carried away with the HTML and CSS.
* Improved the query builder.
* Input sanitisation and validation.
* A few handy DocBlocks here and there.
* ...And (sorry Chris Pitt) there were maybe just a _couple_ errors. But they're gone now. 

## Instructions
#### You will need: 
* Vagrant 
* VirtualBox 

#### Method (sorry):
1. Clone.
2. Do the vagrant stuff.
3. SSH into the machine.
4. Manually run through commands in /vagrant/startup.sh
5. Go to http://localhost:8081/public/ and register for an account! 

#### Thanks!
