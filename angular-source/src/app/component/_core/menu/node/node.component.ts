import { Component, Input, OnInit } from '@angular/core';
import { RouterService } from "../../../../service/router.service";

@Component({
  selector: 'app-menu-node',
  templateUrl: './node.component.html'
})
export class NodeComponent implements OnInit {

	@Input() menuNode;
	@Input() level = 1;

	constructor(public routing: RouterService) { }

	ngOnInit() {

	}
}
