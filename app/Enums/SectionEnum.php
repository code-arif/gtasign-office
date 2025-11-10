<?php
namespace App\Enums;

enum SectionEnum: string {
    const BG = 'bg_image';

    case HOME_BANNER  = 'home_banner';
    case HOME_BANNERS = 'home_banners';

    case HOME_HOW_IT_WORK  = 'home_how_it_work';
    case HOME_HOW_IT_WORKS = 'home_how_it_works';

    case HOME_ABOUT_US  = 'about_us';
    case HOME_ABOUT_USS = 'about_uss';

    case HOME_RECIPE_PAGE  = 'home_recipe_page';
    case HOME_RECIPE_PAGES = 'home_recipe_pages';

    case HOME_CONTACT_US  = 'home_contact_us_page';
    case HOME_CONTACT_USS = 'home_contact_us_pages';

    case PERSONALIZED  = 'personalized';
    case PERSONALIZEDS = 'personalizeds';

    case HERO  = 'hero';
    case HEROS = 'heros';

    case ORDER_AND_DELIVERY_CONTENT  = 'order_and_delivery_content';
    case ORDER_AND_DELIVERY_ITEMS    = 'order_and_delivery_items';

    //Footer
    case FOOTER   = 'footer';
    case SOLUTION = "solution";

}
