# Practical PHP Patterns: Domain Model
https://dzone.com/articles/practical-php-patterns/practical-php-patterns-domain

The architectural pattern I'd like to talk about in this article is the overly famous Domain Model. An application's Domain Model is simply defined as an object graph created from domain-specific classes; when present, a Domain Model is the core of the application, where all the business logic resides. This object graph is employed by upper layers of an application which present it to the user.

## The metaphor for this methodology

In software development, the term domain (or business domain) is an umbrella for the area the application is built in, and that it will serve. The new domains we encounter as we move to new projects are one of the most interesting points of software development, where we are constantly embracing new fields and gaining knowledge.
Given a domain such as a particular industry (chemical, electronics) or business (air travelling, e-commerce), the point of connection of an application with these activities is its model. A model is an abstract representation of the reality of the domain, which captures its interesting and relevant aspects.
The practice of modelling is not a specific trait of software development (in particular model-driven development), but it is a more general scientifical process.
For example, everyone who works in the field of information technology knows the voltage/current relationships for simple components such as resistors and capacitors (Ohm's law and current derivative of the voltage). The specific domain here is electronics, and this model is named lumped component model, essentially because it lets a designer connect isolated one-port (two terminals) components to build his desired circuit.
This model is a simplification of much more complex models of reality: the Maxwell equations and the propagation of electromagnetic fields; the lumped component model is valid whenever the frequency of the voltage/current signals in the circuit is low, so that the wavelengths of these signals are far greater than the dimensions of the circuit (if that goes over your head, don't worry, it's the field of electrical engineers.)
When designers consider larger circuits, such as a transmission line, this model ceases to give correct results and more general ones must be employed. The domain is almost the same, but the model serves a different purpose and has to be necessarily different from the one used in small scale circuits.
This complex example is here only to show that given a domain, there is no single model for it, but there are many possible ones which may adapt more or less reliably to the goals of an application. Starting from a modelling phase and deep understanding of the domain are key points of Domain-Driven Design, one of the ascending methodologies for developing complex enterprise software.

## Software models

While there are standard mathematical models for many domains in the scientific world, software developers usually build a tailored one in every different application, performing an analysis of the domain (or at least they should.)
The result of the modelling can comprehend document or diagrams, but the most powerful artifact is an executable model. Object-oriented programming is a almost perfect paradigm when it comes to modelling the real world, and lets the developers construct a Domain Model in the form of a set of classes.
In a correct implementation of a Domain Model, these classes should be behaviorally complete: they must encapsulate their data as much as possible and expose a set of methods, while avoiding their usage as dumb data containers.
The bread and butter of a Domain Model are the classical example of User, Post, Forum, Group, PrivateMessage classes, which are usually in a one to one relationship with database tables. But the Domain Model is not limited to these Entity classes: it also "comprehends" ValueObjects (modelization of domain-specific data types) and various kinds of Services. Every class that encapsulates business logic is welcome, so that this logic is not duplicated in upper layers, which are the primary clients of the Domain Model.

Dependencies and purity
Another key trait of the classes included in the Domain Model is the absence of external dependencies, like a library to store in the data contained in the objects in a database. The code artifact in a Domain Model are either interfaces, or Plain Old Php Objects (classes which do not extend any external abstract superclass.) Active Record approaches should be avoided because not only a relational database is an infrastructure detail not included in the Domain Model itself, but the very concept of persistence is abstracted away.
As far as the clients of the Domain Model are concerned, the state and behavior of the application are represented by an in-memory object graph, whose methods expose functionalities and which client code can play with. There are no dependencies from a Domain Model towards infrastructure classes, because these dependencies must be inverted.
The resulting system is an instance of the hexagonal architecture, where the Domain Model defines ports (interfaces) and infrastructure can be chosen to provide adapters for these ports (implementations in the form of classes extraneous to the model). The implementaton of non-invasive persistence is the subject of the Data Mapper pattern, which will be treated later in this series, but every kind of service implementation which communicate with the outside of the core object graph (databases, network, filesystem) is only defined as a contract in the Domain Model.
Persistence is almost always dealt with a library in other object-oriented languages, now also in PHP with a non-invasive ORM such as Doctrine 2. Nothing obstructs the developers from implementing a specific Data Mapper by hand, but it's a very repetitive and prone to errors task. While in origin simpler, invasive patterns such as Active Record could be used in a Domain Model, nowadays with Data Mapper availables it is considered an hack.

## Sample

Returning to the subject of the Domain Model as the core of an application, the diffused opinion is that the more complex the business logic and the data involved, the more the application benefits from a rich Domain Model. Thus, this pattern should not be used in small-sized applications where there is no much more logic than CRUD screens for data containers, which unfortunately were a target for PHP in the last ten years. I hope PHP keeps evolving to finally break in the enterprise segment, where this pattern is most valuable.
Due to the size and scope of this article, I am forced to keep the sample code short. Forgive me if you think that you can achieve the same functionality with fewer lines of code, but this pattern is about architecture and should highlight the separation of concerns between classes more than the KISS principle.
Another problem with code samples in modelling is that you have to actually know the domain well to follow the discussion. For this reason I chose a webmail system for this example.

    <?php
    /**
     * Let's suppose we're developing a webmail application, and we 
     * want to create a Domain Model which encapsulates all the logic
     * of sending and receiving mails, creating them, forwarding, managing
     * replies and their visualization, and so on.
     * The most important class in this picture is an Entity which 
     * we'll give the name Email.
     */
    class Email // does not extend anything
    {
        /**
         * We probably want to introduce a class to manage addresses.
         * The modelling phase make decisions such as this, deciding on the issue of
         * primitive variable vs. wrapper class for object fields.
         * For now, we will leave them as simple strings. Subsequent
         * refactoring may introduce wrapper classes or interfaces.
         * @var string
         */
        private $_sender;
        private $_recipient;
    
        private $_subject;
        private $_text;
        
        /**
         * @return string
         */
        public function getSender()
        {
            return $this->_sender;
        }
    
        /**
         * Do we need setters and getters? Every field should be 
         * analyzed. If we can keep it private and inaccessible,
         * it's usually better.
         */
        public function setSender($sender)
        {
            $this->_sender = $sender;
        }
    
        /**
         * @return string
         */
        public function getRecipient()
        {
            return $this->_recipient;
        }
    
        public function setRecipient($recipient)
        {
            $this->_recipient = $recipient;
        }
    
        /**
         * @return string
         */
        public function getSubject()
        {
                return $this->_subject;
        }
    
        public function setSubject($subject)
        {
            $this->_subject = $subject;
        }
    
        /**
         * @return string
         */
        public function getText()
        {
            return $this->_text;
        }
    
        public function setText($text)
        {
            $this->_text = $text;
        }
    
        public function __toString()
        {
            return $this->_subject . ' > ' . substr($this->_text, 0, 20) . '...';
        }
    
        public function reply()
        {
            $reply = new Email();
            $reply->setRecipient($this->_sender);
            $reply->setSender($this->_recipient);
            $reply->setSubject('Re: ' . $this->_subject);
            $reply->setText($this->_sender . " wrote:\n" . $this->_text);
            return $reply;
        }
    }
    
    /**
     * Interface for a service. This is part of the Domain Model,
     * implementations will be plugged in depending on the environment.
     */
    interface EmailRepository
    {
        /**
         * @return array
         * @TypeOf(Email)
         */
        public function getEmailsFor($recipient);
    }
    
    // client code
    $mail = new Email();
    $mail->setSender("alice@example.com");
    $mail->setRecipient("bob@example.com");
    $mail->setSubject('Hello');
    $mail->setText('This is a test of an Email object, which is part of our Domain Model.');
    echo $mail, "\n";
    $reply = $mail->reply();
    echo $reply, "\n";