var grouplinks={
	data:false,
	form:false,
	change_links:function(source,target,value){
		Form.disable($$('form')[0]);
		for (var a=0; this.form[source].options[a]; a++)
			if (this.form[source].options[a].selected)
				this.data[this.form[source].options[a].value]['direct_'+target]=value;
		this._update_forms();
		Form.enable($$('form')[0]);
	},
	_update_forms:function(){
		this._init();
		this._clear_forms();

		(Object.keys(this.data)).each(function(id){
			var fields=[];

			if (this.form.id.getValue() == id) return;
			else if (this.data[id].direct_parent) fields=['parents'];
			else if (this.data[id].direct_child) fields=['children'];
			else if (this.data[id].indirect_parent) fields=['noparents'];
			else if (this.data[id].indirect_child) fields=['nochildren'];
			else fields=['noparents','nochildren'];

			for (var b=0; fields[b] && b < 10; b++)
				this.form[fields[b]].appendChild(new Element('option',{value:this.data[id].id}).update((this.data[id].namespace ? '<strong>'+this.data[id].namespace+':</strong>' : '')+this.data[id].name));
		}.bind(this));

		this.form.links.value=Object.toJSON(this.data);
	},
	_clear_forms:function(){
		this.form.parents.update();
		this.form.noparents.update();
		this.form.children.update();
		this.form.nochildren.update();
	},
	_init:function(){
		if (this.form) return;

		Form.disable($$('form')[0]);

		this.form={
			id:$$('input[name=id]')[0],
			parents:$$('select[name=parents]')[0],
			children:$$('select[name=children]')[0],
			noparents:$$('select[name=noparents]')[0],
			nochildren:$$('select[name=nochildren]')[0],
			links:$$('input[name=links]')[0]
		};

		eval('this.data='+this.form.links.getValue()+';');
		this._update_forms();

		$$('.parentsadd')[0].observe('click',function(){ grouplinks.change_links('noparents','parent',true); });
		$$('.parentsremove')[0].observe('click',function(){ grouplinks.change_links('parents','parent',false); });
		$$('.childrenadd')[0].observe('click',function(){ grouplinks.change_links('nochildren','child',true); });
		$$('.childrenremove')[0].observe('click',function(){ grouplinks.change_links('children','child',false); });

		Form.enable($$('form')[0]);
	}
};

document.observe('dom:loaded',function(){
	grouplinks._init();

	//Form.disable($$('form')[0]);
	//eval('grouplinks.data='+$$('input[name=links]')[0].getValue()+';');

//	new Ajax.Request('?t=getlinks&id='+$$('input[name=id]')[0].getValue(),{
//		onSuccess:function(xhr){
//			eval('grouplinks.data='+xhr.responseText+';');
			//grouplinks._update_forms();

//			$$('.parentsadd')[0].observe('click',function(){ grouplinks.change_links('noparents','parent',true); });
//			$$('.parentsremove')[0].observe('click',function(){ grouplinks.change_links('parents','parent',false); });
//			$$('.childrenadd')[0].observe('click',function(){ grouplinks.change_links('nochildren','child',true); });
//			$$('.childrenremove')[0].observe('click',function(){ grouplinks.change_links('children','child',false); });

		//	Form.enable($$('form')[0]);
//		}
//	});
});
