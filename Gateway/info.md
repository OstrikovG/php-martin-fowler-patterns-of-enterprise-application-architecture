# Practical PHP Patterns: Gateway
https://dzone.com/articles/practical-php-patterns/basic/dnp-practical-php-patterns

A fundamental trait of modern software is that it does not live in isolation, especially in the realm of web applications, which can easily interact with external resources like web services and databases.

The majority of PHP applications must access external resources, that by architecture do not run in the same memory segment or programming language of their core Domain Model. There are many examples of these situations:

web services like Google's or Yahoo! ones.
Relational and NoSQL databases.
The filesystem of the server.
Other web and non-web applications for data interoperability.
I'll call any instance of this external dependency a resource, which is an umbrella term for each item of this list.

## Motivation

When you have to access an external resource, you get an API which you code may call. However accessing an API directly, like a PDO object or a HTTP request stream, presents many issues.

First of all, your application ends up becoming very coupled to the particular product or application instance you're using. There is no room for change, since every resource has its specific API, unless it is a commodity like a relational database.

More subtly, general purpose APIs are designed as catch-all interfaces for providing any functionality, and capturing any use case from every possible client. The entire set of methods becomes a possible requirement of your application, since you cannot instantly easily distinguish the primitives really called by your application from the one ignored.

Moreover, the external resource may use data formats and models different from the ones used by your application. This is the case with relational database used as a storage for object models.

## Implementation

There is an easy solution to these interaction problems, which I feel is never pushed enough. The Gateway pattern is this solution: wrap into a single object all the interaction specifical to the integrated resource, so that your object provides a specialized API of exactly what you want, as you want.

This pattern is similar to the Facade classic one, but it is applied on other people's code instead of our own. You can also compare it to an Adapter, when the Adaptee is not even object-oriented or in the same process of your application's code.

By the way, this pattern is specialized by many other ones, and it can be thought of as their superclass.

## Wrapping

Wrapping is the mechanism used for this pattern's implementation. Only the functionality needed is really exposed from the Gateway.

This minimalism help the Gateway in becoming the target of integration tests or pragmatic unit tests that exercise only the functionalities actually exposed and that may cause a regression. This pattern insulate the application layer or the Domain Model from external changes.

The Hexagonal Architecture is really an evolution of this pattern applied systematically to every external resource, until only an in-memory object structure stands as the core domain, and every dependency is injected as an adapter for an application's port.

A Gateway can also be implemented with more than one object (back end and front end) when the work to do is both on the protocol side (procedural vs. oo, XML vs. variables) and at the workflow side (different slicing of functionalities, APIs at the wrong level of abstraction fro your use case).

## Advantages

I'll never get done with talking of the advantage of introducing a Gateway over an external dependency.

You achieve greater insulation over the dependency: changes do not spread into your system and you can test them separately and efficiently.

The system is also easier to read and understand as it does not pull in the whole complexity of the resource, but only the abstraction needed by client code.

## Disadvantages

There's hardly any downside in coding up a Gateway class, unless you introduce a leaky abstraction.

## Peculiarity

According to Fowler, this pattern is somewhat different from the other integration-related ones, and due to these differences it has earned a name and an article here.

* A Facade simplifies a complex API, and it is written by the developers of the resource used. A Gateway is written by the client code developers to simplify their own job. The Facade also implies a different interface, while Gateway can simply wrap it and transform it or hiding part of it.
* An Adapter alters an implementation to provide a new API. With a Gateway there may not be an existing interface, or if there is, the Adapter is part of the Gateway implementation, which comprehends a back end side.
* A Mediator separates different objects, but Gateway is much more specialized in separating two objects and keeping the dependency side (the external resource) not aware of being used.

## Example

Today's example is a Gateway to a web service, in the form of the classic Twitter client. For simplicity and readability we'll deal only with a single operations that does not require authentication, badly implemented with OAuth by Twitter at the time of this writing.

    <?php
    class TwitterGateway
    {
        /**
         * the only functionality I need from the feed
         */
        public function getLastTweet($username)
        {
            $endPoint = "http://twitter.com/statuses/user_timeline/{$username}.xml?count=1";
            $buffer = file_get_contents($endPoint);
            $xml = new SimpleXMLElement($buffer);
            return $xml->status->text;
        }
    }
    
    // having an object to represent Twitter means we can mock it,
    // pass it around, injecting it, composing it...
    $gateway = new TwitterGateway();
    // client code
    echo $gateway->getLastTweet('giorgiosironi'), "\n"; 