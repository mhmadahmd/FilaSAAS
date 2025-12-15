<?php

namespace Mhmadahmd\Filasaas\Database\Seeders;

use Illuminate\Database\Seeder;
use Mhmadahmd\Filasaas\Models\Feature;
use Mhmadahmd\Filasaas\Models\Plan;

class FilasaasSeeder extends Seeder
{
    /**
     * Get the default currency from config.
     */
    protected function getDefaultCurrency(): string
    {
        return strtoupper(config('filasaas.plans.default_currency', 'USD'));
    }

    /**
     * Get the default trial period from config.
     */
    protected function getDefaultTrialPeriod(): int
    {
        return (int) config('filasaas.plans.default_trial_period', 0);
    }

    /**
     * Get the default trial interval from config.
     */
    protected function getDefaultTrialInterval(): string
    {
        return config('filasaas.plans.default_trial_interval', 'day');
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currency = $this->getDefaultCurrency();
        $defaultTrialPeriod = $this->getDefaultTrialPeriod();
        $defaultTrialInterval = $this->getDefaultTrialInterval();

        // Free Plan
        $freePlan = Plan::create([
            'slug' => 'free',
            'name' => ['en' => 'Free Plan', 'ar' => 'الخطة المجانية'],
            'description' => [
                'en' => 'Perfect for getting started. Try our service with basic features.',
                'ar' => 'مثالي للبدء. جرب خدمتنا بالميزات الأساسية.',
            ],
            'is_active' => true,
            'price' => 0.00,
            'signup_fee' => 0.00,
            'currency' => $currency,
            'trial_period' => 0,
            'trial_interval' => $defaultTrialInterval,
            'invoice_period' => 0,
            'invoice_interval' => 'month',
            'grace_period' => 0,
            'grace_interval' => 'day',
            'cash_auto_approve' => true,
            'allowed_payment_gateways' => ['cash'],
            'sort_order' => 1,
        ]);

        // Basic Plan
        $basicPlan = Plan::create([
            'slug' => 'basic',
            'name' => ['en' => 'Basic Plan', 'ar' => 'الخطة الأساسية'],
            'description' => [
                'en' => 'Ideal for small teams and individual users.',
                'ar' => 'مثالي للفرق الصغيرة والمستخدمين الأفراد.',
            ],
            'is_active' => true,
            'price' => 9.99,
            'signup_fee' => 0.00,
            'currency' => $currency,
            'trial_period' => $defaultTrialPeriod > 0 ? $defaultTrialPeriod : 14,
            'trial_interval' => $defaultTrialInterval,
            'invoice_period' => 1,
            'invoice_interval' => 'month',
            'grace_period' => 7,
            'grace_interval' => 'day',
            'cash_auto_approve' => false,
            'allowed_payment_gateways' => ['cash', 'stripe', 'paypal'],
            'sort_order' => 2,
        ]);

        // Pro Plan
        $proPlan = Plan::create([
            'slug' => 'pro',
            'name' => ['en' => 'Pro Plan', 'ar' => 'الخطة الاحترافية'],
            'description' => [
                'en' => 'Best for growing businesses with advanced features.',
                'ar' => 'الأفضل للشركات النامية بالميزات المتقدمة.',
            ],
            'is_active' => true,
            'price' => 29.99,
            'signup_fee' => 0.00,
            'currency' => $currency,
            'trial_period' => $defaultTrialPeriod > 0 ? $defaultTrialPeriod : 14,
            'trial_interval' => $defaultTrialInterval,
            'invoice_period' => 1,
            'invoice_interval' => 'month',
            'grace_period' => 7,
            'grace_interval' => 'day',
            'cash_auto_approve' => false,
            'allowed_payment_gateways' => ['cash', 'stripe', 'paypal'],
            'sort_order' => 3,
        ]);

        // Enterprise Plan
        $enterprisePlan = Plan::create([
            'slug' => 'enterprise',
            'name' => ['en' => 'Enterprise Plan', 'ar' => 'الخطة المؤسسية'],
            'description' => [
                'en' => 'For large organizations with custom requirements.',
                'ar' => 'للمؤسسات الكبيرة مع متطلبات مخصصة.',
            ],
            'is_active' => true,
            'price' => 99.99,
            'signup_fee' => 0.00,
            'currency' => $currency,
            'trial_period' => $defaultTrialPeriod > 0 ? max($defaultTrialPeriod, 30) : 30,
            'trial_interval' => $defaultTrialInterval,
            'invoice_period' => 1,
            'invoice_interval' => 'month',
            'grace_period' => 14,
            'grace_interval' => 'day',
            'cash_auto_approve' => false,
            'allowed_payment_gateways' => ['cash', 'stripe', 'paypal'],
            'sort_order' => 4,
        ]);

        // Free Plan Features
        $this->createFeature($freePlan, 'users', [
            'name' => ['en' => 'Users', 'ar' => 'المستخدمون'],
            'description' => ['en' => 'Number of users allowed', 'ar' => 'عدد المستخدمين المسموح بهم'],
            'value' => '1',
        ]);

        $this->createFeature($freePlan, 'storage', [
            'name' => ['en' => 'Storage', 'ar' => 'التخزين'],
            'description' => ['en' => 'Storage space in GB', 'ar' => 'مساحة التخزين بالجيجابايت'],
            'value' => '1',
            'resettable_period' => 1,
            'resettable_interval' => 'month',
        ]);

        $this->createFeature($freePlan, 'api_access', [
            'name' => ['en' => 'API Access', 'ar' => 'الوصول إلى API'],
            'description' => ['en' => 'Access to API endpoints', 'ar' => 'الوصول إلى نقاط نهاية API'],
            'value' => 'false',
        ]);

        $this->createFeature($freePlan, 'support', [
            'name' => ['en' => 'Support', 'ar' => 'الدعم'],
            'description' => ['en' => 'Customer support level', 'ar' => 'مستوى دعم العملاء'],
            'value' => 'community',
        ]);

        // Basic Plan Features
        $this->createFeature($basicPlan, 'users', [
            'name' => ['en' => 'Users', 'ar' => 'المستخدمون'],
            'description' => ['en' => 'Number of users allowed', 'ar' => 'عدد المستخدمين المسموح بهم'],
            'value' => '5',
        ]);

        $this->createFeature($basicPlan, 'storage', [
            'name' => ['en' => 'Storage', 'ar' => 'التخزين'],
            'description' => ['en' => 'Storage space in GB', 'ar' => 'مساحة التخزين بالجيجابايت'],
            'value' => '10',
            'resettable_period' => 1,
            'resettable_interval' => 'month',
        ]);

        $this->createFeature($basicPlan, 'api_access', [
            'name' => ['en' => 'API Access', 'ar' => 'الوصول إلى API'],
            'description' => ['en' => 'Access to API endpoints', 'ar' => 'الوصول إلى نقاط نهاية API'],
            'value' => 'true',
        ]);

        $this->createFeature($basicPlan, 'api_requests', [
            'name' => ['en' => 'API Requests', 'ar' => 'طلبات API'],
            'description' => ['en' => 'Monthly API requests limit', 'ar' => 'حد طلبات API الشهرية'],
            'value' => '1000',
            'resettable_period' => 1,
            'resettable_interval' => 'month',
        ]);

        $this->createFeature($basicPlan, 'support', [
            'name' => ['en' => 'Support', 'ar' => 'الدعم'],
            'description' => ['en' => 'Customer support level', 'ar' => 'مستوى دعم العملاء'],
            'value' => 'email',
        ]);

        // Pro Plan Features
        $this->createFeature($proPlan, 'users', [
            'name' => ['en' => 'Users', 'ar' => 'المستخدمون'],
            'description' => ['en' => 'Number of users allowed', 'ar' => 'عدد المستخدمين المسموح بهم'],
            'value' => '25',
        ]);

        $this->createFeature($proPlan, 'storage', [
            'name' => ['en' => 'Storage', 'ar' => 'التخزين'],
            'description' => ['en' => 'Storage space in GB', 'ar' => 'مساحة التخزين بالجيجابايت'],
            'value' => '100',
            'resettable_period' => 1,
            'resettable_interval' => 'month',
        ]);

        $this->createFeature($proPlan, 'api_access', [
            'name' => ['en' => 'API Access', 'ar' => 'الوصول إلى API'],
            'description' => ['en' => 'Access to API endpoints', 'ar' => 'الوصول إلى نقاط نهاية API'],
            'value' => 'true',
        ]);

        $this->createFeature($proPlan, 'api_requests', [
            'name' => ['en' => 'API Requests', 'ar' => 'طلبات API'],
            'description' => ['en' => 'Monthly API requests limit', 'ar' => 'حد طلبات API الشهرية'],
            'value' => '10000',
            'resettable_period' => 1,
            'resettable_interval' => 'month',
        ]);

        $this->createFeature($proPlan, 'advanced_analytics', [
            'name' => ['en' => 'Advanced Analytics', 'ar' => 'التحليلات المتقدمة'],
            'description' => ['en' => 'Access to advanced analytics dashboard', 'ar' => 'الوصول إلى لوحة التحليلات المتقدمة'],
            'value' => 'true',
        ]);

        $this->createFeature($proPlan, 'priority_support', [
            'name' => ['en' => 'Priority Support', 'ar' => 'الدعم ذو الأولوية'],
            'description' => ['en' => 'Priority customer support', 'ar' => 'دعم العملاء ذو الأولوية'],
            'value' => 'true',
        ]);

        $this->createFeature($proPlan, 'support', [
            'name' => ['en' => 'Support', 'ar' => 'الدعم'],
            'description' => ['en' => 'Customer support level', 'ar' => 'مستوى دعم العملاء'],
            'value' => 'priority',
        ]);

        // Enterprise Plan Features
        $this->createFeature($enterprisePlan, 'users', [
            'name' => ['en' => 'Users', 'ar' => 'المستخدمون'],
            'description' => ['en' => 'Number of users allowed', 'ar' => 'عدد المستخدمين المسموح بهم'],
            'value' => 'unlimited',
        ]);

        $this->createFeature($enterprisePlan, 'storage', [
            'name' => ['en' => 'Storage', 'ar' => 'التخزين'],
            'description' => ['en' => 'Storage space in GB', 'ar' => 'مساحة التخزين بالجيجابايت'],
            'value' => 'unlimited',
        ]);

        $this->createFeature($enterprisePlan, 'api_access', [
            'name' => ['en' => 'API Access', 'ar' => 'الوصول إلى API'],
            'description' => ['en' => 'Access to API endpoints', 'ar' => 'الوصول إلى نقاط نهاية API'],
            'value' => 'true',
        ]);

        $this->createFeature($enterprisePlan, 'api_requests', [
            'name' => ['en' => 'API Requests', 'ar' => 'طلبات API'],
            'description' => ['en' => 'Monthly API requests limit', 'ar' => 'حد طلبات API الشهرية'],
            'value' => 'unlimited',
        ]);

        $this->createFeature($enterprisePlan, 'advanced_analytics', [
            'name' => ['en' => 'Advanced Analytics', 'ar' => 'التحليلات المتقدمة'],
            'description' => ['en' => 'Access to advanced analytics dashboard', 'ar' => 'الوصول إلى لوحة التحليلات المتقدمة'],
            'value' => 'true',
        ]);

        $this->createFeature($enterprisePlan, 'custom_integrations', [
            'name' => ['en' => 'Custom Integrations', 'ar' => 'التكاملات المخصصة'],
            'description' => ['en' => 'Custom integrations and API access', 'ar' => 'التكاملات المخصصة والوصول إلى API'],
            'value' => 'true',
        ]);

        $this->createFeature($enterprisePlan, 'dedicated_support', [
            'name' => ['en' => 'Dedicated Support', 'ar' => 'الدعم المخصص'],
            'description' => ['en' => 'Dedicated account manager and support', 'ar' => 'مدير حساب مخصص ودعم'],
            'value' => 'true',
        ]);

        $this->createFeature($enterprisePlan, 'sla_guarantee', [
            'name' => ['en' => 'SLA Guarantee', 'ar' => 'ضمان SLA'],
            'description' => ['en' => 'Service level agreement guarantee', 'ar' => 'ضمان اتفاقية مستوى الخدمة'],
            'value' => 'true',
        ]);

        $this->createFeature($enterprisePlan, 'support', [
            'name' => ['en' => 'Support', 'ar' => 'الدعم'],
            'description' => ['en' => 'Customer support level', 'ar' => 'مستوى دعم العملاء'],
            'value' => 'dedicated',
        ]);
    }

    /**
     * Create a feature for a plan.
     */
    protected function createFeature(Plan $plan, string $slug, array $data): Feature
    {
        return Feature::create([
            'plan_id' => $plan->id,
            'slug' => $slug,
            'name' => $data['name'] ?? ['en' => ucfirst($slug)],
            'description' => $data['description'] ?? null,
            'value' => $data['value'] ?? 'false',
            'resettable_period' => $data['resettable_period'] ?? null,
            'resettable_interval' => $data['resettable_interval'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
        ]);
    }
}

