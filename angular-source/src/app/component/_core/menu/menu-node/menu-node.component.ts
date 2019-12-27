import { Component, Input, OnInit } from '@angular/core';
import { RouterService } from "../../../../service/router.service";
import { MenuNode } from "../../../../model/menu-node";

@Component({
	selector: 'app-menu-node',
	templateUrl: './menu-node.component.html',
	styleUrls: ['./menu-node.component.scss']
})
export class MenuNodeComponent implements OnInit {

	@Input() menuNode: MenuNode;
	@Input() level: number = 1;

	public nodeClass: string = '';
	public subnodeClass: string = '';

	constructor(
		public routing: RouterService
	) { }

	ngOnInit() {}

	/**
	 * Opens/closes menu
	 */
	public toggleMenu(): void {
		this.menuNode.open = !this.menuNode.open;
	}
}
