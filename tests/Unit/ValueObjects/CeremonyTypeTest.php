<?php

namespace Tests\Unit\ValueObjects;

use App\Domain\ValueObjects\CeremonyType;
use PHPUnit\Framework\TestCase;

class CeremonyTypeTest extends TestCase
{
    /** @test */
    public function it_knows_grave_requirements()
    {
        $this->assertTrue(CeremonyType::BURIAL->requiresGrave());
        $this->assertFalse(CeremonyType::CREMATION->requiresGrave());
        $this->assertFalse(CeremonyType::ENTOMBMENT->requiresGrave());
    }

    /** @test */
    public function it_knows_crematorium_requirements()
    {
        $this->assertFalse(CeremonyType::BURIAL->requiresCrematorium());
        $this->assertTrue(CeremonyType::CREMATION->requiresCrematorium());
        $this->assertFalse(CeremonyType::ENTOMBMENT->requiresCrematorium());
    }

    /** @test */
    public function it_knows_mausoleum_requirements()
    {
        $this->assertFalse(CeremonyType::BURIAL->requiresMausoleum());
        $this->assertFalse(CeremonyType::CREMATION->requiresMausoleum());
        $this->assertTrue(CeremonyType::ENTOMBMENT->requiresMausoleum());
    }

    /** @test */
    public function it_has_italian_labels()
    {
        $this->assertEquals('Sepoltura', CeremonyType::BURIAL->label());
        $this->assertEquals('Cremazione', CeremonyType::CREMATION->label());
        $this->assertEquals('Tumulazione', CeremonyType::ENTOMBMENT->label());
    }

    /** @test */
    public function it_knows_typical_products()
    {
        $burialProducts = CeremonyType::BURIAL->typicalProducts();
        $cremationProducts = CeremonyType::CREMATION->typicalProducts();

        $this->assertContains('coffin', $burialProducts);
        $this->assertContains('grave_opening', $burialProducts);

        $this->assertContains('coffin', $cremationProducts);
        $this->assertContains('urn', $cremationProducts);
        $this->assertContains('cremation_permit', $cremationProducts);
    }
}
