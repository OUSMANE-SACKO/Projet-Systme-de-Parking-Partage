<?php
    class SubscribeToSubscriptionUseCase {
        public function execute(Customer $customer, string $subscriptionId, Parking $parking): Subscription {
            if ($subscriptionId === '') {
                throw new InvalidArgumentException('subscriptionId must not be empty');
            }
            
            foreach ($parking->getSubscriptions() as $subscription) {
                if ($subscription->getId() === $subscriptionId) {
                    $customer->addSubscription($subscription);
                    return $subscription;
                } else {
                    throw new InvalidArgumentException('Subscription not found in this parking');
                }
            }
        }
    }
?>