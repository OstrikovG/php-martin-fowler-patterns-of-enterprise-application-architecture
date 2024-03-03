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