<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $fillable = [
        'name',
        'table_name',
        'model_name',
        'icon',
        'order',
        'is_active',
        'fields',
        'relationships',
    ];

    protected $casts = [
        'fields' => 'array',
        'relationships' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the model class for this menu
     */
    public function getModelClass()
    {
        if ($this->model_name && class_exists($this->model_name)) {
            return $this->model_name;
        }

        // Try to auto-detect model
        $modelName = 'App\\Models\\' . str_replace('_', '', ucwords($this->table_name, '_'));
        if (class_exists($modelName)) {
            return $modelName;
        }

        return null;
    }

    /**
     * Get field definitions
     */
    public function getFieldDefinitions()
    {
        return $this->fields ?? [];
    }

    /**
     * Get relationship definitions
     */
    public function getRelationships()
    {
        return $this->relationships ?? [];
    }

    /**
     * Check if a field is a foreign key
     */
    public function isForeignKey($fieldName)
    {
        $relationships = $this->getRelationships();
        foreach ($relationships as $rel) {
            if ($rel['foreign_key'] === $fieldName) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get relationship config for a foreign key
     */
    public function getRelationshipForField($fieldName)
    {
        $relationships = $this->getRelationships();
        foreach ($relationships as $rel) {
            if ($rel['foreign_key'] === $fieldName) {
                return $rel;
            }
        }
        return null;
    }

    /**
     * Scope for active menus
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for ordered menus
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }
}
