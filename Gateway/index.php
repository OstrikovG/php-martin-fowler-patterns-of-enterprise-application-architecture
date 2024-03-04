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