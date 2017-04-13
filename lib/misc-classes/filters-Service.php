<?php

// <editor-fold desc=" ***** Service filters *****" defaultstate="collapsed" >
RQuery::$defaultFilters['service']['refcount']['operators']['>,<,=,!'] = Array(
    'eval' => '$object->countReferences() !operator! !value!',
    'arg' => true
);
RQuery::$defaultFilters['service']['object']['operators']['is.unused'] = Array(
    'Function' => function(ServiceRQueryContext $context )
    {
        return $context->object->countReferences() == 0;
    },
    'arg' => false
);
RQuery::$defaultFilters['service']['object']['operators']['is.unused.recursive'] = Array(
    'Function' => function(ServiceRQueryContext $context )
    {
        $object = $context->object;

        $f = function($ref) use (&$f)
        {
            /** @var Service|ServiceGroup $ref */
            if($ref->countReferences() == 0 )
                return true;

            $groups = $ref->findReferencesWithClass('ServiceGroup');

            if( count($groups) != $ref->countReferences() )
                return false;

            if( count($groups) == 0 )
                return true;

            foreach( $groups as $group )
            {
                /** @var ServiceGroup $group */
                if( $f($group) == false )
                    return false;
            }

            return true;
        };

        return $f($object);

    },
    'arg' => false
);
RQuery::$defaultFilters['service']['object']['operators']['is.member.of'] = Array(
    'Function' => function(ServiceRQueryContext $context )
    {
        $serviceGroup = $context->object->owner->find( $context->value );

        if( $serviceGroup === null )
            return false;

        if( $serviceGroup->hasObjectRecursive( $context->object ) )
            return true;

        return false;

    },
    'arg' => true
);
RQuery::$defaultFilters['service']['name']['operators']['is.in.file'] = Array(
    'Function' => function(ServiceRQueryContext $context )
    {
        $object = $context->object;

        if( !isset($context->cachedList) )
        {
            $text = file_get_contents($context->value);

            if( $text === false )
                derr("cannot open file '{$context->value}");

            $lines = explode("\n", $text);
            foreach( $lines as  $line)
            {
                $line = trim($line);
                if(strlen($line) == 0)
                    continue;
                $list[$line] = true;
            }

            $context->cachedList = &$list;
        }
        else
            $list = &$context->cachedList;

        return isset($list[$object->name()]);
    },
    'arg' => true
);
RQuery::$defaultFilters['service']['object']['operators']['is.group'] = Array(
    'Function' => function(ServiceRQueryContext $context )
    {
        return $context->object->isGroup();
    },
    'arg' => false
);
RQuery::$defaultFilters['service']['object']['operators']['is.tcp'] = Array(
    'Function' => function(ServiceRQueryContext $context )
    {
        $object = $context->object;
        if( $object->isTmpSrv() )
            return false;

        if( $object->isGroup() )
            return false;

        return $context->object->isTcp();
    },
    'arg' => false
);
RQuery::$defaultFilters['service']['object']['operators']['is.udp'] = Array(
    'Function' => function(ServiceRQueryContext $context )
    {
        $object = $context->object;
        if( $object->isTmpSrv() )
            return false;

        if( $object->isGroup() )
            return false;

        return $context->object->isUdp();
    },
    'arg' => false
);
RQuery::$defaultFilters['service']['object']['operators']['is.tmp'] = Array(
    'Function' => function(ServiceRQueryContext $context )
    {
        return $context->object->isTmpSrv();
    },
    'arg' => false
);
RQuery::$defaultFilters['service']['name']['operators']['eq'] = Array(
    'Function' => function(ServiceRQueryContext $context )
    {
        return $context->object->name() == $context->value;
    },
    'arg' => true
);
RQuery::$defaultFilters['service']['name']['operators']['eq.nocase'] = Array(
    'Function' => function(ServiceRQueryContext $context )
    {
        return strtolower($context->object->name()) == strtolower($context->value);
    },
    'arg' => true
);
RQuery::$defaultFilters['service']['name']['operators']['contains'] = Array(
    'Function' => function(ServiceRQueryContext $context )
    {
        return strpos($context->object->name(), $context->value) !== false;
    },
    'arg' => true
);
RQuery::$defaultFilters['service']['name']['operators']['regex'] = Array(
    'Function' => function(ServiceRQueryContext $context )
    {
        $object = $context->object;
        $value = $context->value;

        if( strlen($value) > 0 && $value[0] == '%')
        {
            $value = substr($value, 1);
            if( !isset($context->nestedQueries[$value]) )
                derr("regular expression filter makes reference to unknown string alias '{$value}'");

            $value = $context->nestedQueries[$value];
        }

        $matching = preg_match($value, $object->name());
        if( $matching === FALSE )
            derr("regular expression error on '{$value}'");
        if( $matching === 1 )
            return true;
        return false;
    },
    'arg' => true
);
RQuery::$defaultFilters['service']['members.count']['operators']['>,<,=,!'] = Array(
    'eval' => "\$object->isGroup() && \$object->count() !operator! !value!",
    'arg' => true
);
RQuery::$defaultFilters['service']['tag']['operators']['has'] = Array(
    'Function' => function(ServiceRQueryContext $context )
    {
        return $context->object->tags->hasTag($context->value) === true;
    },
    'arg' => true,
    'argObjectFinder' => "\$objectFind=null;\n\$objectFind=\$object->tags->parentCentralStore->find('!value!');"
);
RQuery::$defaultFilters['service']['tag']['operators']['has.nocase'] = Array(
    'Function' => function(ServiceRQueryContext $context )
    {
        return $context->object->tags->hasTag($context->value, false) === true;
    },
    'arg' => true
);
RQuery::$defaultFilters['service']['tag']['operators']['has.regex'] = Array(
    'Function' => function(ServiceRQueryContext $context )
    {
        foreach($context->object->tags->tags() as $tag )
        {
            $matching = preg_match( $context->value, $tag->name() );
            if( $matching === FALSE )
                derr("regular expression error on '{$context->value}'");
            if( $matching === 1 )
                return true;
        }

        return false;
    },
    'arg' => true,
);
RQuery::$defaultFilters['service']['tag.count']['operators']['>,<,=,!'] = Array(
    'eval' => "\$object->tags->count() !operator! !value!",
    'arg' => true
);
RQuery::$defaultFilters['service']['description']['operators']['regex'] = Array(
    'Function' => function(ServiceRQueryContext $context )
    {
        $object = $context->object;
        $value = $context->value;

        if( strlen($value) > 0 && $value[0] == '%')
        {
            $value = substr($value, 1);
            if( !isset($context->nestedQueries[$value]) )
                derr("regular expression filter makes reference to unknown string alias '{$value}'");

            $value = $context->nestedQueries[$value];
        }

        $matching = preg_match($value, $object->description());
        if( $matching === FALSE )
            derr("regular expression error on '{$value}'");
        if( $matching === 1 )
            return true;
        return false;
    },
    'arg' => true
);
RQuery::$defaultFilters['service']['location']['operators']['is'] = Array(
    'Function' => function(ServiceRQueryContext $context )
    {
        $owner = $context->object->owner->owner;
        if( strtolower($context->value) == 'shared' )
        {
            if( $owner->isPanorama() )
                return true;
            if( $owner->isFirewall() )
                return true;
            return false;
        }
        if( strtolower($context->value) == strtolower($owner->name()) )
            return true;

        return false;
    },
    'arg' => true
);
RQuery::$defaultFilters['service']['location']['operators']['regex'] = Array(
    'Function' => function(ServiceRQueryContext $context )
    {
        $name = $context->object->getLocationString();
        $matching = preg_match($context->value, $name);
        if( $matching === FALSE )
            derr("regular expression error on '{$context->value}'");
        if( $matching === 1 )
            return true;
        return false;
    },
    'arg' => true
);
// </editor-fold>