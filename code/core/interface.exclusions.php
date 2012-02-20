<?php

/**
 * Defines what every type of exclusion for Advanced
 * Post Privacy needs to have in order to function
 * seamlessly with the rest of the system.
 */
interface appExclusions {

	/**
	 * Gets the option name of the exclusion type class,
	 * that will store the data of exclusions for that type.
	 */
	public static function optionName();
	
	/**
	 * Gets the exclusions of that type, if passed an identifier
	 * it will get a specific exclusion, otherwise it will return all of them.
	 */
	public static function getExclusion($identifier=null);

	/**
	 * Sets a single exclusion of the type to its correct place
	 * in the options table.
	 */
    public static function setExclusion($exclusionData);

    /**
     * Removes an exclusion of that type, based on it's identifier, and the
     * key of the post type.
     */
    public static function removeExclusion($identifier, $postTypeArrayKey);

    /**
     * Gets all Post Ids that are excluded based on that exclusion type.
     */
    public static function getExcludedPostIds();
}