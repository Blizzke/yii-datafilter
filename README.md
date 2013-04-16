# Yii Data Filter #

*Alfa code!*
This extension is brandnew and very much in development. Do not expect stable code yet!

It happens often enough that you need to be able to filter data from models.
If you are anything like me, you are probably sick of (re)writing the same code all the time.
Hence this extension. The goal is to develop a generic filter "framework" that can easily be added to any model you want.

## Usage ##

I am currently limiting the information in this section as it's expected to change quite a bit in the future.
There are 3 "entry points" for this extension:

### IFilterable ###

An interface that every model that wants to use the data filter capabilities needs to implement.
It contains 2 functions:

* dataFilterGetOptions: Called to obtain filter-related configuration. It should return an array. The actual content depends on the filter used.
* dataFilterApply: Called to apply rules to the given DbCriteria instance. It receives the relevant filter as well.

### DataFilterer ###

This object contains the actual filters and can serialize itself.
Note: I had the actual filters also serialize themselves as I want to be able to dynamically add new ones. Keep this in mind when creating the filterer.
The 2nd instantiation it probably already contains the filters from the previous run.

### DataFiltererWidget ###

The actual widget that receives the filterer. It can be used in views to output everything.

