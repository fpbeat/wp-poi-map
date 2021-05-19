{if $image}<img src="{$image}"/>{/if}
<div>
    <h1>{$name}</h1>

    {if $address}<p>{$address}</p>{/if}
    {if $phone}<p>{t}Тел{/t}: {$phone}</p>{/if}
    {if $email}<p>{t}E-mail{/t}: {$email}</p>{/if}

    <p><a href="{$permalink}">{t}Детальніше{/t}</a></p>
</div>