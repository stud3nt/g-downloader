import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, NavigationEnd, Router } from "@angular/router";
import { ContentHeaderDataService } from "../../service/data/content-header-data.service";
import { ParserService } from "../../service/parser.service";
import { ParserType } from "../../enum/parser-type";
import { ParserNode } from "../../model/parser-node";
import { ParserRequest } from "../../model/parser-request";
import { ParsedFile } from "../../model/parsed-file";
import { ConfigService } from "../../service/config.service";
import { NodeLevel } from "../../enum/node-level";
import { PageLoaderDataService } from "../../service/data/page-loader-data.service";
import { Pagination } from "../../model/pagination";
import { NodesListComponent } from "./nodes-list/nodes-list.component";
import { NodeStatus } from "../../enum/node-status";
import { RouterService } from "../../service/router.service";
import { PaginationMode } from "../../enum/pagination-mode";
import { webSocket } from "rxjs/webSocket";
import { WebsocketAddress } from "../../enum/websocket-address";
import { HttpHelper } from "../../helper/http-helper";
import { AuthService } from "../../service/auth.service";
import {ToastrDataService} from "../../service/data/toastr-data.service";
import {Status} from "../../model/status";
import {StatusCode} from "../../enum/status-code";
import {JsonResponse} from "../../model/json-response";

@Component({
	selector: 'app-parser',
	templateUrl: './parser.component.html',
	styleUrls: ['./parser.component.scss']
})

export class ParserComponent implements OnInit {

	// current parser name
	protected parserName: string = '';
	// current node level
	protected nodeLevel: string = '';
	// current node identifier
	protected nodeIdentifier: string = '';

	private parserWebsocket;

	protected scrollY = 0;

	public parserRequest: ParserRequest = new ParserRequest();

	public parserRequestAction: boolean = false;

	public NodeLevel = NodeLevel;
	public NodeStatus = NodeStatus;

	public parserBreadcrumbs = [];

	/** Template variables **/
	public actionBeltClass: string = ''; // classes for action belt
	public actionBeltMaskClass: string = ''; // classes for action belt mask
	public scrollTopClass: string = ''; // classes for "scroll top" button
	public previousNodeUrl: string = null;
	public nextNodeUrl: string = null;

	public nodesListComponent: NodesListComponent;

	private filesTemp: ParsedFile[] = [];

	constructor(
		private headerData: ContentHeaderDataService,
		private route: ActivatedRoute,
		private router: Router,
		private auth: AuthService,
		private config: ConfigService,
		private parserService: ParserService,
		private toastrService: ToastrDataService,
		private pageLoaderDataService: PageLoaderDataService,
		public routerService: RouterService
	) {}

	ngOnInit() {
		window.addEventListener('scroll', this.scrollEvent, true);

		this.parserBreadcrumbs = [];
		this.previousNodeUrl = null;
		this.nextNodeUrl = null;

		this.scrollEvent();

		this.determineParserData();
		this.initializeParserRequestObject();
		this.setHeaderData();

		this.sendParserRequest();
	};

	ngOnDestroy() {
		window.removeEventListener('scroll', this.scrollEvent, true);
	}

	/**
	 * Downloads data again for current node;
	 *
	 * @param reloadCache - true if reloading is 'hard' (cache must be refreshed too)
	 */
	public reopenCurrentNode(reloadCache: boolean = false): void {
		this.parserRequest.clearParsedData();
		this.parserRequest.ignoreCache = reloadCache;
		this.sendParserRequest(() => {
			window.scrollTo(0, 0);
		});
	};

	/**
	 * Adds/removes status in current node
	 *
	 * @param status
	 */
	public markCurrentNode(status: string): void {
		this.parserService.markNode(this.parserRequest.currentNode, status).subscribe((response) => {
			this.parserRequest.currentNode.removeStatus(NodeStatus.Waiting);
		}, (error) => {
			this.parserRequest.currentNode.removeStatus(NodeStatus.Waiting);
		});
	};

	/**
	 * Determines classes for current node;
	 *
	 * @param status
	 */
	public getCurrentNodeButtonClass(status: string): string {
		this.nodesListComponent = new NodesListComponent(this.parserService, this.routerService);

		return this.nodesListComponent.getNodeButtonClass(
			this.parserRequest.currentNode, status
		);
	}

	/**
	 * Determines classes for action belt based on current scrollY value;
	 */
	public determineActionBeltClass(): void {
		this.actionBeltClass = 'actionbelt_container' + ((this.scrollY > 80) ? ' fixed' : '');
		this.actionBeltMaskClass = 'actionbelt_mask' + ((this.scrollY > 80) ? ' visible' : '');
		this.scrollTopClass = 'scroll-top-container'+((this.scrollY > 200) ? ' visible' : '');
	}

	public toggleSettingsModal(): void {

	}

	public scrollTop(): void {
		window.scrollTo(0, 0);
	};

	/**
	 * Changes page of currently viewed node.
	 *
	 * @param pagination - pagination settings;
	 */
	public changeNodePage(pagination: Pagination): void {
		this.filesTemp = (pagination.mode === PaginationMode.LoadMore) // new files will be added to current;
			? this.parserRequest.files
			: null;

		this.parserRequest.pagination = pagination;
	 	this.parserRequest.clearParsedData();
		this.sendParserRequest();
	};

	/**
	 * Scrolling event function - saves scroll position and runs classes function for action belt;
	 * @param event
	 */
	protected scrollEvent = (event: any = null): void => {
		this.scrollY = window.scrollY;
		this.determineActionBeltClass();
	};

	/**
	 * Creates ParserRequest model with initial settings;
	 */
	private initializeParserRequestObject() : void {
		this.parserRequest = new ParserRequest();
		this.parserRequest.currentNode = this.initializeParserNodeObject();
		this.parserRequest.apiToken = this.auth.user.apiToken;
	};

	/**
	 * Creates initial ParserNodeObject with absolutely basic datas;
	 */
	private initializeParserNodeObject() : ParserNode {
		let node = new ParserNode();

		node.parser = this.parserName;
		node.level = this.nodeLevel;
		node.identifier = this.nodeIdentifier;

		return node;
	};

	/**
	 * Sends data to parser API
	 */
	private sendParserRequest(successFunction: () => any = null, errorFunction: () => any = null, completeFunction: () => any = null): void {
		if (this.parserRequestAction) // only one action at same time
			return;

		this.parserRequestAction = true;
		this.filesTemp = this.parserRequest.files;

		this.parserService.sendParserActionRequest(this.parserRequest).subscribe((response : ParserRequest) => {
			this.parserRequest = response;
			this.previousNodeUrl = (response.previousNode) ? this.routerService.generateNodeUrl(response.previousNode) : null;
			this.nextNodeUrl = (response.nextNode) ? this.routerService.generateNodeUrl(response.nextNode) : null;
			this.parserRequestAction = false;

			if (this.filesTemp) // adding new files at end of list;
				this.parserRequest.files = [...this.filesTemp, ...this.parserRequest.files];

			if (successFunction)
				successFunction();
		}, () => {
			this.pageLoaderDataService.hide();
			this.parserRequestAction = false;

			if (errorFunction)
				errorFunction();
		}, () => {
			this.pageLoaderDataService.setProgress(100).hide();
			this.parserRequestAction = false;

			if (completeFunction)
				completeFunction();
		});

		setTimeout(() => {
			this.parserStatusListener();
		}, 200);
	}

	private parserStatusListener(): void {
		if (!this.parserWebsocket) {
			this.parserWebsocket = webSocket(WebsocketAddress.ParsingNode);
			this.parserWebsocket.subscribe((response) => {
				let recursive = true;

				if (typeof response === 'object') {
					let jsonResponse = new JsonResponse(response);

					if (jsonResponse.success()) {
						let status = new Status(jsonResponse.data);

						switch (status.code) {
							case StatusCode.NoEffect:
							case StatusCode.OperationStarted:
								this.pageLoaderDataService.show().setProgress(status.progress);
								break;

							case StatusCode.OperationInProgress:
								this.pageLoaderDataService.show().setProgress(status.progress);
								break;

							case StatusCode.OperationEnded:
								this.pageLoaderDataService.hide();
								recursive = false;
								break;
						}
					}
				} else {
					this.pageLoaderDataService.hide();
				}

				if (recursive) {
					setTimeout(() => {
						this.parserStatusListener();
					}, 250);
				}
			}, (error) => {
				this.toastrService.addError('PARSER ERROR', error.message);
				console.log(error);
			}, () => {

			});
		}

		this.parserWebsocket.next({
			_token: this.auth.user.apiToken,
			parserRequest: (HttpHelper.convert(this.parserRequest, HttpHelper.Object))
		});
	}

	/**
	 * Determines parser name based by URL address;
	 */
	private determineParserData() : void {
		let urlArray = this.router.url.split('/');

		urlArray.forEach((value, index) => {
			if (value === 'parsers') {
				this.parserName = urlArray[(index+1)];
				this.nodeLevel = (typeof urlArray[(index+2)] !== 'undefined') ? urlArray[(index+2)] : null;
				this.nodeIdentifier = (typeof urlArray[(index+3)] !== 'undefined') ? urlArray[(index+3)] : null;
			}
		});
	}

	/**
	 * Sets data for content-header
	 *
	 * IMPORTANT: data musts set AFTER initialize parser board data
	 */
	private setHeaderData() : void {
		let parserEnumData = ParserType.getData();

		this.headerData.setElement('title1', 'Parser');
		this.headerData.setElement('title2', 'Scrapping images and movies');
		this.headerData.clearBreadcrumbs();
		this.headerData.addBreadcrumb({
			route: null,
			routeParams: {'parserName':this.parserName},
			label: 'Parsers',
			icon: 'fa-dashboard'
		});
		this.headerData.addBreadcrumb({
			route: 'app_parser',
			routeParams: {'parserName':this.parserName},
			label: (typeof parserEnumData[this.parserName] !== 'undefined')
				? parserEnumData[this.parserName]
				: null,
			icon: 'fa-dashboard'
		});
	}

}