<?php

namespace App\DTOs;

class WebmasterStatsDTO extends BaseDTO
{
    public ?string $site_name = null;
    public ?string $site_url = null;
    public ?int $total_clicks = null;
    public ?int $total_impressions = null;
    public ?float $average_ctr = null;
    public ?float $average_position = null;
    public ?int $indexed_pages = null;
    public ?int $excluded_pages = null;
    public ?string $period_start = null;
    public ?string $period_end = null;
    public ?array $search_queries = null;
    public ?array $crawl_errors = null;
    public ?array $external_links = null;
    public ?array $indexing = null;
    public ?float $average_load_time = null;

    public function validate(): bool
    {
        return !empty($this->site_url) && !empty($this->site_name);
    }

    public function getMainMetrics(): array
    {
        return [
            'clicks' => $this->total_clicks ?? 0,
            'impressions' => $this->total_impressions ?? 0,
            'ctr' => $this->average_ctr ?? 0,
            'position' => $this->average_position ?? 0,
            'indexed_pages' => $this->indexed_pages ?? 0,
            'excluded_pages' => $this->excluded_pages ?? 0,
        ];
    }

    public function hasGoodSeoPerformance(): bool
    {
        return ($this->average_ctr ?? 0) > 2.0 &&
               ($this->average_position ?? 100) < 10;
    }

    public function getFormattedCtr(): string
    {
        if (!$this->average_ctr) {
            return '0%';
        }

        return round($this->average_ctr, 2) . '%';
    }

    public function getFormattedPosition(): string
    {
        if (!$this->average_position) {
            return 'N/A';
        }

        return round($this->average_position, 1);
    }

    public function getTopSearchQuery(): ?string
    {
        if (empty($this->search_queries)) {
            return null;
        }

        $top = collect($this->search_queries)->sortByDesc('clicks')->first();
        return $top['query'] ?? null;
    }

    public function getIndexingRate(): float
    {
        $total = ($this->indexed_pages ?? 0) + ($this->excluded_pages ?? 0);

        if ($total === 0) {
            return 0;
        }

        return round((($this->indexed_pages ?? 0) / $total) * 100, 2);
    }

    public function hasIndexingIssues(): bool
    {
        return $this->getIndexingRate() < 80 ||
               ($this->excluded_pages ?? 0) > ($this->indexed_pages ?? 0);
    }

    public function hasCrawlErrors(): bool
    {
        return !empty($this->crawl_errors) && count($this->crawl_errors) > 0;
    }

    public function getErrorsCount(): int
    {
        return count($this->crawl_errors ?? []);
    }

    public function getBacklinksCount(): int
    {
        return count($this->external_links ?? []);
    }

    public function getFormattedLoadTime(): string
    {
        if (!$this->average_load_time) {
            return 'N/A';
        }

        if ($this->average_load_time < 1) {
            return round($this->average_load_time * 1000) . ' мс';
        }

        return round($this->average_load_time, 2) . ' сек';
    }

    public function hasSlowLoading(): bool
    {
        return ($this->average_load_time ?? 0) > 3; // Медленная загрузка если больше 3 секунд
    }
}
