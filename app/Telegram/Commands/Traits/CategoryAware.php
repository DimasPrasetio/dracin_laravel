<?php

namespace App\Telegram\Commands\Traits;

use App\Models\Category;
use App\Services\TelegramService;

trait CategoryAware
{
    protected ?Category $category = null;

    /**
     * Resolve category from local state or shared container context.
     */
    protected function resolveCategory(): ?Category
    {
        if ($this->category instanceof Category) {
            return $this->category;
        }

        if (app()->bound('telegram.category')) {
            $category = app('telegram.category');
            if ($category instanceof Category) {
                $this->category = $category;
            }
        }

        return $this->category;
    }

    /**
     * Set the category context for this command
     */
    public function setCategory(?Category $category): self
    {
        $this->category = $category;
        return $this;
    }

    /**
     * Get the current category context
     */
    public function getCategory(): ?Category
    {
        return $this->resolveCategory();
    }

    /**
     * Get telegram service with category context
     */
    protected function getTelegramService(): TelegramService
    {
        $service = app(TelegramService::class);

        $category = $this->resolveCategory();
        if ($category) {
            $service->setCategory($category);
        }

        return $service;
    }

    /**
     * Get category name for display
     */
    protected function getCategoryName(): string
    {
        return $this->resolveCategory()?->name ?? 'Default';
    }

    /**
     * Get channel ID for current category
     */
    protected function getChannelId(): ?string
    {
        return $this->resolveCategory()?->channel_id ?? config('telegram.channel_id');
    }
}
