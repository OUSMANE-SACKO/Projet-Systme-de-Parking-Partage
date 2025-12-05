<?php
    class SubscribeToSubscriptionUseCase {
        public function execute(Customer $customer, string $subscriptionId, Parking $parking): Subscription {
            if (trim($subscriptionId) === '') {
                throw new InvalidArgumentException('subscriptionId must not be empty');
            }
            
            foreach ($parking->getSubscriptions() as $subscription) {
                if ($subscription->getId() === $subscriptionId) {
                    if (method_exists($customer, 'getSubscriptions')) {
                        foreach ($customer->getSubscriptions() as $existing) {
                            if ($existing->getId() === $subscriptionId) {
                                return $existing;
                            }
                        }
                    }
                    $customer->addSubscription($subscription);
                    return $subscription;
                }
            }
            throw new InvalidArgumentException('Subscription not found in this parking');
        }
    }
?>