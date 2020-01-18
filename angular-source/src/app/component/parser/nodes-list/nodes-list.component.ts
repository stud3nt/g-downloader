import { Component, Input } from '@angular/core';
import { ParserRequest } from "../../../model/parser-request";
import { NodeStatus } from "../../../enum/node-status";
import { ParserService } from "../../../service/parser.service";
import { ParserNode } from "../../../model/parser-node";
import { RouterService } from "../../../service/router.service";

@Component({
  selector: 'app-nodes-list',
  templateUrl: './nodes-list.component.html'
})
export class NodesListComponent {

	@Input() parserRequest: ParserRequest;

	public NodeStatus = NodeStatus;

	// controller - if true, all tiles are locked (non-clickable);
	public lockTiles = false;

	constructor(
		private parserService: ParserService,
		public routerService: RouterService
	) { }

	/**
	 * Marks node with specified status;
	 *
	 * @param node
	 * @param status
	 */
	public markNode(node: ParserNode, status: string): void {
		let request = this.parserService.markNode(node, status);

		if (!request)
			return;

		request.subscribe((response) => {
			this.parserRequest.currentNode = node; // re-assign current node object
			node.removeStatus(NodeStatus.Waiting);
		}, (error) => {
			node.removeStatus(NodeStatus.Waiting);
		});
	}

	/**
	 * Determines class for specified node based on status;
	 *
	 * @param node
	 * @return string
	 */
	public getNodeClass(node: ParserNode): string {
		let nodeClass = 'tile tile-250';

		if (node.hasStatus(NodeStatus.Saved))
			nodeClass += ' saved';

		if (node.hasStatus(NodeStatus.Downloaded))
			nodeClass += ' downloaded';

		return nodeClass;
	}

	/**
	 * Determines node button class based on checked status;
	 *
	 * @param node
	 * @param status
	 * @param size
	 * @return string
	 */
	public getNodeButtonClass(node: ParserNode, status: string, size: string = 'normal'): string {
		let buttonClasses = 'btn';

		switch (size) {
			case 'normal':
				break;

			case 'small':
				buttonClasses += ' btn-sm';
				break;
		}

		if (node.hasStatus(status))
			buttonClasses += (' '+NodeStatus.buttonStatusClass(status));
		else
			buttonClasses += ' btn-default';

		return buttonClasses;
	}
}
