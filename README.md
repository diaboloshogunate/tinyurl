# Tiny URL in symfony
This was built for a code challenge that requires generating a random, unique 5-9 length alphanumeric string.

# Installation
Standard symfony project using `encore/webpack` with `yarn` 

1. download and place on php 7.4+x64 server of your choice or use `symfony server:start` for a local dev build after doing the below
2. create database and add credentials to .env
3. `composer install`
4. `php bin/console doctrine:migrations:migrate`
5. `yarn install`
6. `yarn encore production`

The routes are `http://server/`, `http://server/view/{short}` and `http://server/{short}`

# The method used
There are multiple ways one can gnerate this string.

## Simple case
The simplest case is to just start at `aaaaa` and increment. 
I am not choosing this method because it makes it easy to guess other urls and doesn't satisfy the random requirement in my oponion.

## The usual method
I think the way most people would approach this is:
1. Generate a number for the length
2. Make an array of a-z0-9
3. Select a random character until you meat the satisfied length.

This method is OK but with equipped with the power of math this can be improved on.

## Using math method
Each position in the string has 36 possible values `a-z0-9`
There are `36^5` possible 5 length strings, `36^6` possible 6 length strings etc.
In total there are `36^5+36^6+36^7+36^8+36^9` or `104461667988480` possible values. 

**Note that this number is large enough to where this will only work on 64 bit machines which is standard these days. I may opt to use the previous method if on a 32 bit machine.**

Instead of randomly selecting a character 5-9 times, using the known possible values you only have to generate 1 random value and use mods and bit shifts to convert it to a string.

I am using `0=a...35=9` for my int to string indexes and using recursion over a loop to improve even more on performance.

For the random number, you need to subtract 1 because of 0-indexing to avoid pesky off by one error.

For generating the string, we want 0 to be `aaaaa` not `a`. 
Noting that `36^1=aa`, `36^2+36^1=aaa` etc. all we need to do is add `36^4+36^3+36^2+36^1` or `1727604` to the number so that the `0=aaaaa` requirement is met.

### Database structure
I am structuring the database for this method. 
I have the auto increment id set to big int since int can't handle the range of values needed for all the possibilities.

I have the short url set to unique which gives it an index for faster lookup.
 
## Guaranteed uniqueness 
Neither of these methods guarantees uniqueness so the database is checked to see if the value is taken. If it is try again.

The likelihood of collisions is small until a lot of values have been taken. 

If you want to guarantee a unique value every time, you would need to build all the values and select one that is not used.

I am not doing this simply because of how many possible values there are.
 
The values can easily be generated with fixutres and stored in a csv or in the database. 
If using the database, it would be more efficient to have two tables.
One of available values and another taken values, allowing you can select a random available value more quickly. 

## Additional notes
- If I add the other methods as options I will allow them to be chosen via config values
- I may consider having it automatically switch from the math method to the guaranteed method once there are enough values for collisions to take place every 10 or so attempts on average