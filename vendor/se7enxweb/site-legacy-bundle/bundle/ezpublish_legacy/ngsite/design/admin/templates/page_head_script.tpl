{* The page's configuration for Exponential UI (its server call and preference addresses, siteaccess, locale and
   texts), as the kernel's own admin page head writes it. Without it the exp:: scripts call /ezjscore/call/ of the
   default siteaccess: the sub-items list of the legacy admin ran in the site's siteaccess. Only on a kernel that
   requires the expui extension; older kernels have no design:page_head_exp.tpl. *}
{if ezini_hasvariable( 'ExtensionSettings', 'RequiredExtensions', 'site.ini' )}
    {if ezini( 'ExtensionSettings', 'RequiredExtensions', 'site.ini' )|contains( 'expui' )}
        {include uri='design:page_head_exp.tpl'}
    {/if}
{/if}
{ezscript_load( ezini( 'JavaScriptSettings', 'BackendJavaScriptList', 'design.ini' )|prepend( 'ezjsc::jquery', 'ezjsc::jqueryio' ) )}
