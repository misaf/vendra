<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Filament\Navigation;

enum NavigationPriority: int
{
    case Products = 1100;
    case ProductCategories = 1110;
    case ProductPrices = 1120;
    case Attributes = 1200;

    case Transactions = 2100;
    case TransactionGateways = 2110;
    case Wallets = 2120;
    case Currencies = 2200;
    case Carts = 2300;
    case Orders = 2400;
    case DeliveryZones = 2500;
    case DeliverySlots = 2510;
    case Deliveries = 2520;

    case Users = 3100;
    case UserProfiles = 3110;
    case Wishlists = 3120;
    case Inquiries = 3130;
    case Roles = 3200;
    case Permissions = 3210;

    case BlogPosts = 4100;
    case BlogPostCategories = 4110;
    case CustomPages = 4200;
    case CustomPageCategories = 4210;
    case Faqs = 4300;
    case FaqCategories = 4310;
    case Multimedia = 4400;
    case Tags = 4500;

    case Affiliates = 5100;
    case AffiliateCommissions = 5110;
    case AffiliatePayouts = 5120;
    case Newsletters = 5200;
    case NewsletterSubscribers = 5210;

    case Languages = 6100;
    case LanguageLines = 6110;

    case GeneralSettings = 7100;
    case StorefrontSettings = 7110;
    case OrderSettings = 7120;
    case CartSettings = 7130;
    case DeliverySettings = 7140;
    case AffiliateSettings = 7150;
    case InquirySettings = 7160;
    case WishlistSettings = 7170;
    case ActivityLogs = 7200;
}
