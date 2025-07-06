<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Client;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clients = [
            [
                'name' => 'TechCorp Solutions',
                'email' => 'contact@techcorp.com',
                'phone' => '+1-555-0101',
                'company' => 'TechCorp Solutions Inc.',
                'address' => '123 Tech Street, Silicon Valley, CA 94000',
                'notes' => 'Leading technology company specializing in enterprise software solutions.',
                'is_active' => true,
            ],
            [
                'name' => 'Creative Agency',
                'email' => 'hello@creativeagency.com',
                'phone' => '+1-555-0102',
                'company' => 'Creative Agency LLC',
                'address' => '456 Design Ave, New York, NY 10001',
                'notes' => 'Full-service creative agency focusing on branding and digital marketing.',
                'is_active' => true,
            ],
            [
                'name' => 'StartupXYZ',
                'email' => 'founders@startupxyz.com',
                'phone' => '+1-555-0103',
                'company' => 'StartupXYZ',
                'address' => '789 Innovation Blvd, Austin, TX 78701',
                'notes' => 'Fast-growing startup in the fintech space.',
                'is_active' => true,
            ],
            [
                'name' => 'Global Enterprises',
                'email' => 'projects@globalent.com',
                'phone' => '+1-555-0104',
                'company' => 'Global Enterprises Corp',
                'address' => '321 Corporate Plaza, Chicago, IL 60601',
                'notes' => 'Multinational corporation with diverse business interests.',
                'is_active' => true,
            ],
            [
                'name' => 'Local Business',
                'email' => 'owner@localbiz.com',
                'phone' => '+1-555-0105',
                'company' => 'Local Business Co.',
                'address' => '654 Main Street, Smalltown, OH 43001',
                'notes' => 'Family-owned business looking to expand their digital presence.',
                'is_active' => true,
            ],
        ];

        foreach ($clients as $clientData) {
            Client::create($clientData);
        }
    }
}
