<?php
    
    namespace Nnjeim\World\Models\Traits;
    
    trait WorldConnection
    {
        /**
         * Get the connection associated with the model.
         *
         * @return string
         */
        public function getConnectionName(): string
        {
            return config('world.connection');
        }
    }
