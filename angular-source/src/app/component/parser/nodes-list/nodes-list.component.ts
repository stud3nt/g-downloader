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

	public lockTiles = false;

	public scrollY = 0;

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
		this.parserService.markNode(node).subscribe((response) => {
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
	 * @return string
	 */
	public getNodeButtonClass(node: ParserNode, status: string): string {
		return 'btn ' + (
			(node.hasStatus(status))
				? NodeStatus.buttonStatusClass(status)
				: 'btn-default'
			);
	}
}
