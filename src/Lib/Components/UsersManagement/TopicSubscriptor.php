<?php

namespace App\Lib\Components\UsersManagement;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\UserNotificationTopicSubscription;

class TopicSubscriptor
{
    public function __construct(protected EntityManagerInterface $em)
    {
    }

    /**
     * Returns the list of topics which the user is subscribed to
     *
     * @param User $user
     * @return array
     */
    public function subscribed(User $user): array
    {
        //Retrieve subscribed topics
        $subscribedTopics = $user->getNotificationTopicSubscriptions();
        $cleanTopics = [];
        foreach ($subscribedTopics as $subscribedTopic) {
            $cleanTopics[] = $subscribedTopic->getTopic();
        }
        return $cleanTopics;
    }

    /**
     * Subscribes the user to all topics in the list
     *
     * @param User $user
     * @param array $topics
     * @return void
     */
    public function subscribe(User $user, array $topics)
    {
        $userTopicRepo = $this->em->getRepository(UserNotificationTopicSubscription::class);

        //We check everyone and add it if it is not already into
        foreach($topics as $topic){
            $topic = trim(strip_tags($topic));
            if(!empty($topic)){
                $userTopic = $userTopicRepo->findOneBy(['user' => $user, 'topic' => $topic]);
                if($userTopic == null){
                    $userTopic = new UserNotificationTopicSubscription();
                    $userTopic->setTopic($topic);
                    $userTopic->setUser($user);
                    $this->em->persist($userTopic);
                    $this->em->flush();
                    $user->addNotificationTopicSubscription($userTopic);
                }
            }
        }
        $this->em->persist($user);
        $this->em->flush();
    }

    /**
     * Unsubscribes the user from all topics in the list
     *
     * @param User $user
     * @param array $topics
     * @return void
     */
    public function unsubscribe(User $user, array $topics)
    {
        $userTopicRepo = $this->em->getRepository(UserNotificationTopicSubscription::class);

        //For each one we try to find it related to the user, if it could be found, it must be deleted
        foreach($topics as $topic){
            $topic = trim(strip_tags($topic));
            if(!empty($topic)){
                if($topic == "*"){ //If topic is *, it means wildcard, all topics, clear
                    $userTopics = $userTopicRepo->findBy(['user' => $user]);
                    foreach($userTopics as $userTopic){
                        $this->em->remove($userTopic);
                    }
                    break;
                }
                else{
                    $userTopic = $userTopicRepo->findOneBy(['user' => $user, 'topic' => $topic]);
                    if($userTopic != null){
                        $this->em->remove($userTopic);
                    }
                }
            }
        }
        $this->em->flush();
    }
}
