# mvc-primer

## What's this then?
Just what everyone wanted, a new  PHP Framework! Well, sort of, but not really. 

It's (pretty much) the framework from Pro PHP MVC by Chris Pitt (2012) but a bit modernised and (kind of) ready for PHP 8.0. And it works! There's a Vagrant file & accompanying set of commands to get the framework up and running (LAMP on CentOs/7). That's it really!

### "A bit modernised"?
* Autoloading          > _Replaced with composer_
* MySQLi               > _Replaced with PDO_
* Deprecated functions > _Replaced with non-deprecated alternatives_
* ~~array()             > Replaced with \[\]~~
* Genral Formatting    > _Generally better_

### Anything else? 
Yes! 

I got a little carried away with the HTML and CSS.

I improved the query builder slightly

Added a few handy DocBlocks here and there... 

aaaand... 

also...

(sorry Chris Pitt) there were... maybe just a few errors. But they're gone now. 

## Instructions
#### You will need: 
* Vagrant 
* VirtualBox 

#### Method (sorry):
1. Clone
2. Do the vagrant stuff 
3. SSH into the machine
4. Manually run through commands in /vagrant/startup.sh
5. Go to http://localhost:8081/public/ and register for an account! 

#### Thanks!
