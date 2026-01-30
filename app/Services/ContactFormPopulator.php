<?php

namespace App\Services;

use App\Models\Contact;

class ContactFormPopulator
{
    /**
     * Populate billing/customer fields from a contact.
     *
     * @return array{customer_name: string|null, customer_address: string|null, customer_postal_code: string|null, customer_city: string|null, customer_country: string, payment_terms_days: int}
     */
    public function populateCustomerFields(Contact $contact): array
    {
        return [
            'customer_name' => $contact->company_name,
            'customer_address' => $contact->billing_address ?? $contact->address,
            'customer_postal_code' => $contact->billing_postal_code ?? $contact->postal_code,
            'customer_city' => $contact->billing_city ?? $contact->city,
            'customer_country' => $contact->billing_country ?? $contact->country ?? 'Norge',
            'payment_terms_days' => $contact->payment_terms_days ?? 30,
        ];
    }

    /**
     * Populate delivery fields from a contact.
     *
     * @return array{delivery_address: string|null, delivery_postal_code: string|null, delivery_city: string|null, delivery_country: string}
     */
    public function populateDeliveryFields(Contact $contact): array
    {
        return [
            'delivery_address' => $contact->delivery_address ?? $contact->address,
            'delivery_postal_code' => $contact->delivery_postal_code ?? $contact->postal_code,
            'delivery_city' => $contact->delivery_city ?? $contact->city,
            'delivery_country' => $contact->delivery_country ?? $contact->country ?? 'Norge',
        ];
    }

    /**
     * Populate both customer and delivery fields from a contact.
     *
     * @return array{customer_name: string|null, customer_address: string|null, customer_postal_code: string|null, customer_city: string|null, customer_country: string, payment_terms_days: int, delivery_address: string|null, delivery_postal_code: string|null, delivery_city: string|null, delivery_country: string}
     */
    public function populateAllFields(Contact $contact): array
    {
        return array_merge(
            $this->populateCustomerFields($contact),
            $this->populateDeliveryFields($contact)
        );
    }

    /**
     * Populate invoice-specific fields from a contact (uses billing address fallback).
     *
     * @return array{customer_name: string|null, customer_address: string|null, customer_postal_code: string|null, customer_city: string|null, customer_country: string, payment_terms_days: int}
     */
    public function populateForInvoice(Contact $contact): array
    {
        return $this->populateCustomerFields($contact);
    }

    /**
     * Populate order-specific fields from a contact (customer + delivery).
     *
     * @return array{customer_name: string|null, customer_address: string|null, customer_postal_code: string|null, customer_city: string|null, customer_country: string, payment_terms_days: int, delivery_address: string|null, delivery_postal_code: string|null, delivery_city: string|null, delivery_country: string}
     */
    public function populateForOrder(Contact $contact): array
    {
        return $this->populateAllFields($contact);
    }

    /**
     * Populate quote-specific fields from a contact (customer only).
     *
     * @return array{customer_name: string|null, customer_address: string|null, customer_postal_code: string|null, customer_city: string|null, customer_country: string, payment_terms_days: int}
     */
    public function populateForQuote(Contact $contact): array
    {
        return $this->populateCustomerFields($contact);
    }
}
